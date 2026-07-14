# Telegram bot setup

TerminRadar uses Telegram polling locally. That means the local WAMP site does not need a public HTTPS webhook while you test it on `terminradar.local`.

Official references:

- Bot creation and token: https://core.telegram.org/bots/tutorial
- Bot API methods `getUpdates` and `sendMessage`: https://core.telegram.org/bots/api

## 1. Create the bot

1. Open Telegram and search for `@BotFather`.
2. Send `/newbot`.
3. Choose a display name, for example `TerminRadar`.
4. Choose a username ending with `bot`, for example `terminradar_marl_bot`.
5. Copy the token that BotFather gives you.

Keep the token private. It works like a password for the bot.

## 2. Add the token to the app

Open the local `.env` file and add:

```env
TELEGRAM_BOT_TOKEN=123456789:replace_with_the_real_token
```

Do not commit `.env` to Git.

## 3. Link a user chat

1. Log in to TerminRadar.
2. Open `/dashboard`.
3. In the Telegram card click the button for a link code.
4. Open your Telegram bot and send it exactly that code, for example `A1B2C3`.
5. In the project folder run:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe bin\console telegram:poll
```

Expected result:

```json
{"processed":1,"linked":1,"error":null}
```

The dashboard should then show Telegram as connected.

## 4. Send real notifications

After a Watch is created with Telegram enabled, run the source check:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe bin\console appointments:check-source 1
```

Then send pending Telegram notifications:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe bin\console notifications:send
```

TerminRadar sends a notification when the Watch gets its first current best slot, and later only when a newly found matching slot is earlier than the best slot already stored for that Watch.

## 5. Production cron

For production, run these commands by cron every few minutes:

```bash
php bin/console telegram:poll
php bin/console appointments:check
php bin/console notifications:send
```

Polling and webhooks are mutually exclusive in Telegram. If a webhook was configured before, remove it before using polling.
