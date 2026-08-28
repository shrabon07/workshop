<?php
/**
 * Aurora Cyber — chatbot brain (auto-responder)
 * Lightest-possible keyword engine that answers in the visitor's language,
 * pulls live services from the DB, and can trigger an admin takeover.
 */

declare(strict_types=1);

function bot_service_catalog(int $limit = 4): string
{
    $rows = DB::all(
        'SELECT s.title_en, s.title_bn, s.price, s.price_label
           FROM services s
          WHERE s.status = "active"
          ORDER BY s.sort_order ASC, s.id ASC
          LIMIT ' . (int) $limit
    );
    if (!$rows) {
        return 'We currently have active packages — ask our team for a custom quote.';
    }
    $lines = [];
    foreach ($rows as $r) {
        $price = price_fmt($r['price']);
        $lines[] = '• ' . $r['title_en'] . ' / ' . $r['title_bn'] . ' — ' . $r['price_label'] . ' ' . $price;
    }
    return "Here are our most popular packages:\n" . implode("\n", $lines);
}

/**
 * Decide the bot's next reply for a raw message.
 * Returns ['reply' => string, 'suggestions' => array].
 */
function bot_reply(string $message, array $ctx): array
{
    $m = mb_strtolower(trim($message));
    $s = [];

    $greet = ['hi', 'hello', 'hey', 'salam', 'assalam', 'আসসালাম', 'হ্যালো', 'হাই', 'হেলো', 'good morning', 'good evening', 'gie', 'shuvo'];
    $price = ['price', 'pricing', 'cost', 'charge', 'rate', 'koto', 'মূল্য', 'দাম', 'কত', 'খরচ', 'রেট', 'প্যাকেজ'];
    $service = ['service', 'services', 'offer', 'package', 'সার্ভিস', 'অফার', 'প্যাকেজ', 'কী কী', 'কি কি', 'কি নির্মাণ'];
    $order = ['order', 'track', 'status', 'অর্ডার', 'ট্র্যাক', 'স্ট্যাটাস', 'কোথায়'];
    $human = ['human', 'agent', 'staff', 'manager', 'talk to', 'speak to', 'মানুষ', 'এজেন্ট', 'ম্যানেজার', 'কথা বলতে', 'কাস্টমার'];
    $work = ['portfolio', 'work', 'example', 'sample', 'client', 'পোর্টফোলিও', 'কাজ', 'নমুনা', 'উদাহরণ', 'ক্লায়েন্ট'];
    $thanks = ['thank', 'thanks', 'ধন্যবাদ', 'thanku', 'thank you'];

    foreach ($greet as $w)  if (str_contains($m, $w)) { $s['intro'] = true; break; }
    foreach ($price as $w)  if (str_contains($m, $w)) { $s['price'] = true; break; }
    foreach ($service as $w) if (str_contains($m, $w)) { $s['service'] = true; break; }
    foreach ($order as $w)  if (str_contains($m, $w)) { $s['order'] = true; break; }
    foreach ($human as $w)  if (str_contains($m, $w)) { $s['human'] = true; break; }
    foreach ($work as $w)   if (str_contains($m, $w)) { $s['work'] = true; break; }
    foreach ($thanks as $w) if (str_contains($m, $w)) { $s['thanks'] = true; break; }

    $suggestions = [
        ['key' => 'services', 'label_en' => 'Our services & pricing', 'label_bn' => 'আমাদের সার্ভিস ও মূল্য'],
        ['key' => 'order', 'label_en' => 'Track my order', 'label_bn' => 'অর্ডার ট্র্যাক করুন'],
        ['key' => 'human', 'label_en' => 'Talk to a human', 'label_bn' => 'মানুষের সাথে কথা বলুন'],
    ];

    if (!empty($s['human'])) {
        return [
            'reply' => 'Of course — I am connecting you with a team member right now. Please hold on 🙏',
            'suggestions' => [],
            'handoff' => true,
        ];
    }

    if (!empty($s['order'])) {
        if (!empty($ctx['user_id'])) {
            $o = DB::get('SELECT id, project_type, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$ctx['user_id']]);
            if ($o) {
                $meta = order_status_meta($o['status']);
                return [
                    'reply' => "Your latest order #" . $o['id'] . " (“" . ($o['project_type'] ?: 'Custom project') . "”) is currently: " . $meta['label_en'] . " / " . $meta['label_bn'] . ".",
                    'suggestions' => $suggestions,
                ];
            }
            return ['reply' => 'I could not find any orders on your account yet. You can start one from the "Start a project" section!', 'suggestions' => $suggestions];
        }
        return [
            'reply' => 'To track an order you will need to sign in (the order form remembers your email). Or just message us on WhatsApp with your name and we will look it up.',
            'suggestions' => $suggestions,
        ];
    }

    if (!empty($s['price']) && !empty($s['service'])) {
        return ['reply' => bot_service_catalog(), 'suggestions' => $suggestions];
    }
    if (!empty($s['price']) || !empty($s['service'])) {
        return ['reply' => bot_service_catalog(), 'suggestions' => $suggestions];
    }

    if (!empty($s['work'])) {
        return [
            'reply' => 'We have shipped e-commerce stores, SaaS dashboards and portfolios across Bangladesh. Check the Portfolio section on our homepage, or send us your idea for a free consultation!',
            'suggestions' => $suggestions,
        ];
    }

    if (!empty($s['thanks'])) {
        return ['reply' => 'You are welcome! 🎉 Anything else I can help with?', 'suggestions' => $suggestions];
    }

    if (!empty($s['intro'])) {
        return [
            'reply' => 'Hi there! 👋 Welcome to Aurora Cyber — we build fast, modern websites in Bangladesh. How can I help you today?',
            'suggestions' => $suggestions,
        ];
    }

    // catch-all fallback
    return [
        'reply' => "Got it! I can help you with:\n• Our services & pricing\n• Tracking an order\n• Talking to a human\n\nOr you can write a bit more — I am just a friendly bot 🙂",
        'suggestions' => $suggestions,
    ];
}