## 🧭 About This Package

This package was originally created for personal use to simplify repetitive setup tasks in CodeIgniter 4 projects. Instead of manually configuring each new project, I wanted a way to install everything with Composer and publish all the necessary resources at once. That's how cirebonweb/core was born.

Over time, I realized that this structure could also be useful for others—especially those managing multiple CodeIgniter 4 projects with shared features like login pages, dashboards, admin panels and more. So, I decided to share it publicly, as a small thank you to the CodeIgniter 4 community that has helped me grow as a developer.

## 🧩 Design Philosophy

- This is not a global framework. It’s a personal vendor structure tailored for maintainability across multiple projects.
- Some views are not published to the root project by default. These are core features (like login dashboards) meant to be maintained centrally and published selectively.
- The term “dashboard” here refers to ready-to-use pages with built-in features like login tracking, statistics, data tables, and reset actions—not just a layout shell.

## ⚠️ Notes for Users

- The structure may look unconventional, but it follows CI4 standards and works reliably.
- If you find the layout unfamiliar, remember: this started as a personal tool, not a global solution.
- Documentation may look polished, but I’m still learning—transitioning from CMS WordPress to MVC frameworks has been a journey, and this package reflects that growth.

## 🙏 Final Thoughts

This project is a reflection of real-world pain points and the desire to build maintainable, scalable systems. If it helps you, I’m grateful. If you want to contribute or adapt it, even better.