# 🏊💦 Swinder 💦🏊

### 🔥 Tinder. But wet. 💧

> *✨ entirely vibe coded on the Claude mobile app 📱 no thoughts, just swipes 🫧*

---

## 🤔 what is this

you know how tinder exists? 💘 and you know how swimming pools exist? 🏊

**what if those two things... became one thing.** 🤯

swipe right on pools 👉 match with pools 💞 fall in love with pools 😍 get ghosted by pools 💀 the full experience 🎢

---

## ⚡ features

- 👈👉 **swipe on pools** — hot or not but make it chlorine ☢️
- 🔍 **search any pool** — find a specific pool worldwide, ignores your location filter
- 📍 **location picker** — search by city, use GPS, set your radius (1–50 km)
- 🗺️ **pool map** — see every pool you've rated plotted on a map, colour-coded by score
- 🏆 **leaderboard** — hottest 🔥 and cursed 💀 pools ranked by global swipe ratio
- 📸 **photo cycling** — tap left/right on the card image to browse all pool photos
- 😄 **emoji reactions** — react with 💦🔥🥶🤢💎🏆😱🦆, watch them float away
- ⭐ **real pool data** — pulled live from Google Places with actual photos and ratings
- 💾 **smart caching** — pools cached in SQLite so the API only gets hit once per venue

---

## 🛠️ tech stack

vanilla PHP 🐘 + SQLite 🗄️ + Google Places API 📍 + Leaflet 🗺️

no framework. vibes-driven development. we'll figure it out 🤷

---

## 🚀 deploying on Railway

1. Fork this repo
2. Connect to **railway.app** → New Project → Deploy from GitHub
3. Add environment variable: `GOOGLE_PLACES_API_KEY`
4. Add a persistent volume mounted at `/app/data`
5. Visit `/setup.php` to initialise the DB and fetch your first batch of pools
6. Start swiping 🏊

> You'll need a Google Places API key from [console.cloud.google.com](https://console.cloud.google.com). Enable the **Places API**. The $200/month free credit covers this easily for personal use.

---

## 💻 running locally

```bash
cp config.php.example config.php
# add your Google Places API key to config.php

php -S localhost:8000
# visit localhost:8000/setup.php, then localhost:8000
```

---

## 📖 origin story

this entire project was conceived and vibe coded using the **Claude mobile app** 📱✨ no laptop 💻 no IDE 🖥️ no adult supervision 🚨 just a phone ☎️, a dream 💭, and an AI that said "sure why not" 🤖💅

this is what peak software engineering looks like 🏔️👑

---

## 🤝 contributing

PRs welcome 🙏 please maintain the vibe ✌️💫

---

## 📜 license

[Unlicense](LICENSE) — free as a pool on a hot day ☀️🏊
