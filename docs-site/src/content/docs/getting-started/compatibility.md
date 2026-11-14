---
title: Compatibility
description: Supported PHP, Laravel and Filament versions.
---

| | Version |
|---|---|
| PHP | 8.2 or newer |
| Laravel | 12 or 13 |
| Filament | 5.8.1 or newer |

The package depends on `filament/filament`: it registers a panel plugin and reads the panel's navigation.

The stylesheet uses CSS nesting and `:has()`, which every browser Filament 5 supports understands. It is compiled by your own theme build, so it follows your Tailwind and Vite versions.

[`mortalkiller/filament-complete-user-profile`](https://github.com/mortalkiller/filament-complete-user-profile) is optional. See [Configuration](/filament-appearance/guides/configuration/#with-filament-complete-user-profile) for how the two fit together.
