<?php
function classifyTicket($text) {
    $text = strtolower($text);

    $categories = [
        'Hardware Issue' => ['hardware', 'printer', 'monitor', 'keyboard', 'mouse', 'screen', 'laptop', 'computer', 'device', 'broken', 'physical', 'power', 'battery', 'charger', 'usb', 'cable', 'display'],
        'Software Issue' => ['software', 'install', 'update', 'crash', 'bug', 'error', 'application', 'app', 'program', 'windows', 'mac', 'operating system', 'os', 'driver', 'license', 'activation', 'compatibility'],
        'Network Issue' => ['network', 'internet', 'wifi', 'wi-fi', 'connection', 'vpn', 'dns', 'ip', 'firewall', 'router', 'slow connection', 'disconnect', 'bandwidth', 'ethernet', 'proxy', 'server down'],
        'Billing Issue' => ['bill', 'billing', 'payment', 'invoice', 'charge', 'refund', 'subscription', 'price', 'cost', 'fee', 'overcharge', 'credit', 'debit', 'account balance', 'plan'],
        'Technical Support' => ['technical', 'support', 'help', 'assist', 'troubleshoot', 'fix', 'resolve', 'configure', 'setup', 'setting', 'how to', 'guide', 'reset', 'recover'],
        'General Inquiry' => ['general', 'question', 'info', 'information', 'inquiry', 'feedback', 'suggestion', 'complaint', 'other', 'hello', 'hi']
    ];

    $scores = [];
    foreach ($categories as $category => $keywords) {
        $score = 0;
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $score++;
            }
        }
        $scores[$category] = $score;
    }

    arsort($scores);
    $best = key($scores);

    return $scores[$best] > 0 ? $best : 'General Inquiry';
}

function analyzeSentiment($text) {
    $text = strtolower($text);

    $negative_words = ['angry', 'frustrated', 'terrible', 'worst', 'horrible', 'unacceptable', 'disgusting', 'furious', 'annoyed', 'pathetic', 'useless', 'broken', 'failed', 'urgent', 'emergency', 'critical', 'immediately', 'asap', 'hate', 'awful', 'disappointed', 'outraged', 'ridiculous', 'stuck', 'not working', 'dead', 'down', 'crash', 'lost', 'damage', 'never', 'worst ever', 'scam'];

    $positive_words = ['thank', 'thanks', 'good', 'great', 'excellent', 'wonderful', 'amazing', 'appreciate', 'happy', 'satisfied', 'love', 'perfect', 'awesome', 'fantastic', 'helpful', 'pleased', 'nice', 'well done', 'impressive', 'brilliant'];

    $neg_score = 0;
    $pos_score = 0;

    foreach ($negative_words as $word) {
        if (strpos($text, $word) !== false) {
            $neg_score++;
        }
    }

    foreach ($positive_words as $word) {
        if (strpos($text, $word) !== false) {
            $pos_score++;
        }
    }

    if ($neg_score > $pos_score) {
        return 'negative';
    } elseif ($pos_score > $neg_score) {
        return 'positive';
    } else {
        return 'neutral';
    }
}

function suggestPriority($sentiment, $text) {
    $text = strtolower($text);
    $urgent_words = ['urgent', 'emergency', 'critical', 'immediately', 'asap', 'down', 'crashed', 'not working', 'dead', 'blocked', 'stuck', 'lost data'];

    foreach ($urgent_words as $word) {
        if (strpos($text, $word) !== false) {
            return 'high';
        }
    }

    if ($sentiment === 'negative') {
        return 'high';
    } elseif ($sentiment === 'neutral') {
        return 'medium';
    } else {
        return 'low';
    }
}
?>