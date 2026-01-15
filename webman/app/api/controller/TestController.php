<?php

namespace app\api\controller;

use Illuminate\Support\Facades\Http;

class TestController
{
    public function test()
    {
        return success();
    }

    public function sendDingtalk()
    {
        $reportData = [
            'stats' => [
                [
                    'word' => '二狗日报',
                    'count' => 15,
                    'titles' => [
                        [
                            'title' => '昨夜二狗在王者十连跪是道德的沦丧吗',
                            'source_name' => '科技新闻',
                            'ranks' => [1, 3],
                            'rank_threshold' => 10,
                            'url' => 'http://example.com',
                            'mobile_url' => '',
                            'time_display' => '09:00',
                            'count' => 2,
                            'is_new' => true
                        ]
                    ]
                ]
            ],
            'new_titles' => [],
            'failed_ids' => []
        ];

        $messageData = self::buildMessageData($reportData, '当日汇总');
//        dump($messageData);
//        $format =  json_encode($messageData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $URL='https://oapi.dingtalk.com/robot/send?access_token=8b1cdc6cd509df3040b638f9b12af9bd4185a956449f594e42cc7aacd7aa2228888';
        $res = Http::asJson()->post($URL,$messageData);
        $responseBody = $res->json(); // 获取响应体
        if(isset($responseBody['errcode']) && $responseBody['errcode'] === 0) {
            // 发送完全成功
            echo "消息发送成功";
        } else {
            // 发送失败，可以查看具体的错误信息
            echo "消息发送失败: " . ($responseBody['errmsg'] ?? '未知错误');
        }
        return success(['data' => $res]);


    }


    /**
     * 构建钉钉机器人消息数据
     *
     * @param array $reportData 报告数据
     * @param string $reportType 报告类型
     * @param array|null $updateInfo 更新信息
     * @param string $mode 模式
     * @return array 钉钉消息数据结构
     */
    public static function buildMessageData($reportData, $reportType, $updateInfo = null, $mode = "daily") {
        $content = self::renderDingtalkContent($reportData, $updateInfo, $mode);

        return [
            "msgtype" => "markdown",
            "markdown" => [
                "title" => "TrendRadar 热点分析报告 - " . $reportType,
                "text" => $content
            ]
        ];
    }



    /**
     * 渲染钉钉内容
     *
     * @param array $reportData 报告数据
     * @param array|null $updateInfo 更新信息
     * @param string $mode 模式
     * @return string 格式化后的内容
     */
    private static function renderDingtalkContent($reportData, $updateInfo, $mode) {
        $text_content = "";
        $total_titles = 0;

        if (!empty($reportData['stats'])) {
            foreach ($reportData['stats'] as $stat) {
                if ($stat['count'] > 0) {
                    $total_titles += count($stat['titles']);
                }
            }
        }

        $text_content .= "**总新闻数：** " . $total_titles . "\n\n";
        $text_content .= "**时间：** " . date('Y-m-d H:i:s') . "\n\n";
        $text_content .= "**类型：** 热点分析报告\n\n";
        $text_content .= "---\n\n";

        if (!empty($reportData['stats'])) {
            $text_content .= "📊 **热点词汇统计**\n\n";
            $total_count = count($reportData['stats']);

            foreach ($reportData['stats'] as $i => $stat) {
                $word = $stat['word'];
                $count = $stat['count'];
                $sequence_display = "[" . ($i + 1) . "/" . $total_count . "]";

                if ($count >= 10) {
                    $text_content .= "🔥 " . $sequence_display . " **" . $word . "** : **" . $count . "** 条\n\n";
                } elseif ($count >= 5) {
                    $text_content .= "📈 " . $sequence_display . " **" . $word . "** : **" . $count . "** 条\n\n";
                } else {
                    $text_content .= "📌 " . $sequence_display . " **" . $word . "** : " . $count . " 条\n\n";
                }

                foreach ($stat['titles'] as $j => $title_data) {
                    $formatted_title = self::formatTitleForPlatform("dingtalk", $title_data, true);
                    $text_content .= "  " . ($j + 1) . ". " . $formatted_title . "\n";

                    if ($j < count($stat['titles']) - 1) {
                        $text_content .= "\n";
                    }
                }

                if ($i < count($reportData['stats']) - 1) {
                    $text_content .= "\n---\n\n";
                }
            }
        }

        if (empty($reportData['stats'])) {
            if ($mode == "incremental") {
                $mode_text = "增量模式下暂无新增匹配的热点词汇";
            } elseif ($mode == "current") {
                $mode_text = "当前榜单模式下暂无匹配的热点词汇";
            } else {
                $mode_text = "暂无匹配的热点词汇";
            }
            $text_content .= "📭 " . $mode_text . "\n\n";
        }

        if (!empty($reportData['new_titles'])) {
            if ($text_content && !strpos($text_content, "暂无匹配")) {
                $text_content .= "\n---\n\n";
            }

            $total_new_count = 0;
            foreach ($reportData['new_titles'] as $source_data) {
                $total_new_count += count($source_data['titles']);
            }

            $text_content .= "🆕 **本次新增热点新闻** (共 " . $total_new_count . " 条)\n\n";

            foreach ($reportData['new_titles'] as $source_data) {
                $text_content .= "**" . $source_data['source_name'] . "** (" . count($source_data['titles']) . " 条):\n\n";

                foreach ($source_data['titles'] as $j => $title_data) {
                    $title_data_copy = $title_data;
                    $title_data_copy['is_new'] = false;
                    $formatted_title = self::formatTitleForPlatform("dingtalk", $title_data_copy, false);
                    $text_content .= "  " . ($j + 1) . ". " . $formatted_title . "\n";
                }

                $text_content .= "\n";
            }
        }

        if (!empty($reportData['failed_ids'])) {
            if ($text_content && !strpos($text_content, "暂无匹配")) {
                $text_content .= "\n---\n\n";
            }

            $text_content .= "⚠️ **数据获取失败的平台：**\n\n";
            foreach ($reportData['failed_ids'] as $i => $id_value) {
                $text_content .= "  • **" . $id_value . "**\n";
            }
        }

        $text_content .= "\n\n> 更新时间：" . date('Y-m-d H:i:s');

        if ($updateInfo) {
            $text_content .= "\n> TrendRadar 发现新版本 **" . $updateInfo['remote_version'] . "**，当前 " . $updateInfo['current_version'];
        }

        return $text_content;
    }

    /**
     * 格式化标题用于不同平台
     *
     * @param string $platform 平台名称
     * @param array $titleData 标题数据
     * @param bool $showSource 是否显示来源
     * @return string 格式化后的标题
     */
    private static function formatTitleForPlatform($platform, $titleData, $showSource) {
        $rank_display = self::formatRankDisplay(
            $titleData['ranks'],
            $titleData['rank_threshold'],
            $platform
        );

        $link_url = !empty($titleData['mobile_url']) ? $titleData['mobile_url'] : $titleData['url'];
        $cleaned_title = self::cleanTitle($titleData['title']);

        if ($link_url) {
            $formatted_title = "[" . $cleaned_title . "](" . $link_url . ")";
        } else {
            $formatted_title = $cleaned_title;
        }

        $title_prefix = !empty($titleData['is_new']) ? "🆕 " : "";

        if ($showSource) {
            $result = "[" . $titleData['source_name'] . "] " . $title_prefix . $formatted_title;
        } else {
            $result = $title_prefix . $formatted_title;
        }

        if ($rank_display) {
            $result .= " " . $rank_display;
        }

        if (!empty($titleData['time_display'])) {
            $result .= " - " . $titleData['time_display'];
        }

        if ($titleData['count'] > 1) {
            $result .= " (" . $titleData['count'] . "次)";
        }

        return $result;
    }

    /**
     * 格式化排名显示
     *
     * @param array $ranks 排名数组
     * @param int $rankThreshold 阈值
     * @param string $platform 平台
     * @return string 格式化后的排名
     */
    private static function formatRankDisplay($ranks, $rankThreshold, $platform) {
        if (empty($ranks)) {
            return "";
        }

        $unique_ranks = array_unique($ranks);
        sort($unique_ranks);
        $min_rank = $unique_ranks[0];
        $max_rank = $unique_ranks[count($unique_ranks) - 1];

        if ($platform == "dingtalk") {
            $highlight_start = "**";
            $highlight_end = "**";
        } else {
            $highlight_start = "**";
            $highlight_end = "**";
        }

        if ($min_rank <= $rankThreshold) {
            if ($min_rank == $max_rank) {
                return $highlight_start . "[" . $min_rank . "]" . $highlight_end;
            } else {
                return $highlight_start . "[" . $min_rank . " - " . $max_rank . "]" . $highlight_end;
            }
        } else {
            if ($min_rank == $max_rank) {
                return "[" . $min_rank . "]";
            } else {
                return "[" . $min_rank . " - " . $max_rank . "]";
            }
        }
    }

    /**
     * 清理标题
     *
     * @param string $title 标题
     * @return string 清理后的标题
     */
    private static function cleanTitle($title) {
        if (!is_string($title)) {
            $title = strval($title);
        }

        $cleaned_title = str_replace(["\n", "\r"], " ", $title);
        $cleaned_title = preg_replace('/\s+/', ' ', $cleaned_title);
        return trim($cleaned_title);
    }



}