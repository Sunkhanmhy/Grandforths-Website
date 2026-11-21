<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/resend.php';

apply_cors_headers();
require_post_method();
enforce_rate_limit('newsletter', 8, 600);

$fields = $_POST;
if (is_honeypot_triggered($fields)) {
    json_response(true, 'Thank you for subscribing.');
}

$email = sanitize_text((string) ($fields['email'] ?? ''), 254);
$name = sanitize_text((string) ($fields['name'] ?? ''), 120);

if (!is_valid_email($email)) {
    json_response(false, 'Please provide a valid email address.', 422);
}

$companyEmail = env('COMPANY_EMAIL', 'info@grandforthlegal.com');
$safeName = e($name !== '' ? $name : '—');
$safeEmail = e($email);
$safeDisplayName = e($name !== '' ? $name : 'Website visitor');

$internalHtml = <<<HTML
<h2>New Newsletter Subscriber</h2>
<p><strong>Name:</strong> {$safeName}</p>
<p><strong>Email:</strong> {$safeEmail}</p>
<p>Source: Grandforth Legal Practitioners website newsletter form.</p>
HTML;

resend_send_email($companyEmail, 'New Newsletter Subscriber', $internalHtml, $email);

$confirmationHtml = <<<HTML
<h2>Welcome to the Grandforth Legal Practitioners Insights list</h2>
<p>Dear {$safeDisplayName},</p>
<p>Thank you for subscribing to our newsletter. You will now receive periodic legal
insights, regulatory alerts and firm news covering Nigerian corporate, commercial and
regulatory law directly in your inbox.</p>
<p>Warm regards,<br/>Grandforth Legal Practitioners</p>
HTML;

resend_send_email($email, "You're subscribed — Grandforth Legal Practitioners", $confirmationHtml);

json_response(true, 'Thank you for subscribing — please check your inbox for a confirmation email.');
