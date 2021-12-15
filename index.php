<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once('./LINEBotTiny.php');
require_once('./simple_html_dom.php');

$channelAccessToken = getenv('LINEBOT_CHANNEL_TOKEN') ?: '';
$channelSecret = getenv('LINEBOT_CHANNEL_SECRET') ?: '';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    $text = explode(' ', strtoupper(trim($message['text'])));
                    $reply = '';

                    switch ($text[0]) {
                        case 'GOLD':
                            $html = file_get_html('https://www.goldtraders.or.th/UpdatePriceList.aspx');
                            $reply .= "=: ทองแท่ง :=" . "\n";
                            $reply .= "[B] " . $html->find('#DetailPlace_MainGridView td[align=right]', 0)->plaintext . "\n";
                            $reply .= "[S] " . $html->find('#DetailPlace_MainGridView td[align=right]', 1)->plaintext . "\n";
                            $reply .= "\n";
                            $reply .= "=: ทองรูปพรรณ :=" . "\n";
                            $reply .= "[B] " . $html->find('#DetailPlace_MainGridView td[align=right]', 2)->plaintext . "\n";
                            $reply .= "[S] " . $html->find('#DetailPlace_MainGridView td[align=right]', 3)->plaintext;
                            break;
                        case 'RATE':
                            if (count($text) > 1) {
                                $html = file_get_html('http://www.grandsuperrich.com/rate_fordemo_v3.php');
                                foreach ($html->find('td') as $data)
                                {
                                    if ($text[1] == "USD" && $data->plaintext == 'USD 100')
                                    {
                                        $reply .= "=: USD :=" . "\n";
                                        $reply .= "[B] " . $data->next_sibling()->plaintext . "\n";
                                        $reply .= "[S] " . $data->next_sibling()->next_sibling()->plaintext;
                                        break;
                                    }
                                    else if ($text[1] == "JPY" && $data->plaintext == 'JPY 10000 - 5000')
                                    {
                                        $reply .= "=: JPY :=" . "\n";
                                        $reply .= "[B] " . $data->next_sibling()->plaintext . "\n";
                                        $reply .= "[S] " . $data->next_sibling()->next_sibling()->plaintext;
                                        break;
                                    }
                                    else if ($text[1] == "KRW" && $data->plaintext == 'KRW 50000-5000')
                                    {
                                        $reply .= "=: KRW :=" . "\n";
                                        $reply .= "[B] " . $data->next_sibling()->plaintext . "\n";
                                        $reply .= "[S] " . $data->next_sibling()->next_sibling()->plaintext;
                                        break;
                                    }
                                }
                            } else {
                                $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [
                                        [
                                            'type' => 'text',
                                            'text' => "Please select currency",
                                            "quickReply" => [
                                                "items" => [
                                                    [
                                                        "type" => "action",
                                                        "action" => [
                                                            "type" => "message",
                                                            "label" => "USD",
                                                            "text" => "RATE USD"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "action",
                                                        "action" => [
                                                            "type" => "message",
                                                            "label" => "JPY",
                                                            "text" => "RATE JPY"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "action",
                                                        "action" => [
                                                            "type" => "message",
                                                            "label" => "KRW",
                                                            "text" => "RATE KRW"
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]);
                            }
                            break;
                        default:
                            break;
                    }

                    $client->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $reply
                            ]
                        ]
                    ]);
                    break;
                default:
                    error_log('Unsupported message type: ' . $message['type']);
                    break;
            }
            break;
        default:
            error_log('Unsupported event type: ' . $event['type']);
            break;
    }
};
