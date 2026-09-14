import { json, error } from '@sveltejs/kit';
import { env } from '$env/dynamic/private';
import type { RequestHandler } from './$types';

const MAX_NAME = 100;
const MAX_EMAIL = 254;
const MAX_SUBJECT = 200;
const MAX_MESSAGE = 5000;

export const POST: RequestHandler = async ({ request }) => {
  const apiKey = env.BREVO_API_KEY;
  if (!apiKey) {
    throw error(503, 'Email service not configured.');
  }

  let payload: Record<string, unknown>;
  try {
    payload = await request.json();
  } catch {
    throw error(400, 'Invalid request body.');
  }

  const name = typeof payload.name === 'string' ? payload.name.trim() : '';
  const email = typeof payload.email === 'string' ? payload.email.trim() : '';
  const subject = typeof payload.subject === 'string' ? payload.subject.trim() : '';
  const message = typeof payload.message === 'string' ? payload.message.trim() : '';

  if (
    !name || !email || !subject || !message ||
    name.length > MAX_NAME || email.length > MAX_EMAIL ||
    subject.length > MAX_SUBJECT || message.length > MAX_MESSAGE ||
    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  ) {
    throw error(400, 'Please fill in all fields with valid values.');
  }

  const senderEmail = (env.CONTACT_SENDER ?? '').trim() || 'elevatemediaproductions1@gmail.com';
  const recipient = (env.CONTACT_TO ?? '').trim() || 'elevatemediaproductions1@gmail.com';
  const senderName = env.CONTACT_SENDER_NAME ?? 'Elevate Media Productions';

  const body = {
    sender: { name: senderName, email: senderEmail },
    to: [{ email: recipient, name: 'Elevate Media Productions' }],
    replyTo: { email, name },
    subject: `New website message: ${subject}`,
    htmlContent: `
      <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#0f172a;">
        <p style="font-size:22px;font-weight:700;margin:0 0 16px;">New message from the website</p>
        <table style="border-collapse:collapse;width:100%;">
          <tr><td style="padding:8px 0;color:#64748b;width:100px;vertical-align:top;">From</td><td style="padding:8px 0;"><strong>${escapeHtml(name)}</strong> &lt;${escapeHtml(email)}&gt;</td></tr>
          <tr><td style="padding:8px 0;color:#64748b;width:100px;vertical-align:top;">Subject</td><td style="padding:8px 0;"><strong>${escapeHtml(subject)}</strong></td></tr>
          <tr><td style="padding:8px 0;color:#64748b;width:100px;vertical-align:top;">Message</td><td style="padding:8px 0;"><pre style="white-space:pre-wrap;font-family:inherit;margin:0;line-height:1.6;">${escapeHtml(message)}</pre></td></tr>
        </table>
        <p style="color:#94a3b8;font-size:12px;margin-top:24px;">Sent via elevate-media-productions.vercel.app</p>
      </div>`,
    textContent: `New message from the website\n\nFrom: ${name} <${email}>\nSubject: ${subject}\n\n${message}`
  };

  const res = await fetch('https://api.brevo.com/v3/smtp/email', {
    method: 'POST',
    headers: {
      'api-key': apiKey,
      'accept': 'application/json',
      'content-type': 'application/json'
    },
    body: JSON.stringify(body)
  });

  if (!res.ok) {
    const detail = await res.text().catch(() => '');
    console.error('Brevo send failed', res.status, detail);
    throw error(502, 'Could not send the message right now. Please try again later.');
  }

  return json({ ok: true });
};

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}