# 🤝 CONTRIBUTING

Thank you for your interest in contributing to **cirebonweb/core**!

This package is designed to support maintainers, interns, and developers working with CodeIgniter 4. It includes modular publishing commands and customizable admin panels using AdminLTE and Shield authentication.

---

## 🗂️ Documentation map

| Document | Audience | Contents |
|---|---|---|
| `README.md` | package consumer | Features, installation, usage |
| `ABOUT.md` | anyone | Project story and design philosophy |
| `COMMAND.md` | package consumer | Publish options per group + resulting file tree |
| `ARSITEKTUR.md` | core maintainer | Design rules and publish policy — **main reference** |
| `src/Dokumen/Konsep.md` | core maintainer | CORE module index and concept reference |
| `src/Dokumen/modul-hak-akses.md` | core maintainer | Access-control module decisions and behavior |

### Where does documentation belong?

- General CORE concepts and module index → `src/Dokumen/Konsep.md`.
- Detailed module decisions → a focused `src/Dokumen/modul-*.md` linked from `Konsep.md`.
- Design rules or publish policy → `ARSITEKTUR.md`.
- Publish flags, file trees, manual config → `COMMAND.md`.
- Consumer-facing install steps → `README.md`.

---

## 🧭 Philosophy

We prioritize:
- Maintainability and clarity over cleverness
- Documentation that educates, not just explains
- Helpers and modules that are auditable, override-friendly, and scalable
- Local developer empowerment through Bahasa Indonesia PHPDoc comments

---

## 🛠️ How to Contribute

You can contribute in many ways:
- Improve or translate documentation (especially PHPDoc blocks)
- Refactor or extend helpers and publishing logic
- Report bugs or suggest enhancements via [GitHub Issues](https://github.com/cirebonweb/core/issues)
- Submit pull requests for new features or fixes

---

## 📚 Guidelines

- Use PSR-12 coding standards
- Keep helpers modular and testable
- Prefer plain text documentation blocks for maintainers and interns
- Avoid magic logic—make everything traceable and override-friendly
- If adding new publishable resources, update `COMMAND.md` and `README.md`
- If changing publish behaviour or design rules, update `ARSITEKTUR.md`
- Core PHP comments are Bahasa Indonesia; repository documents (`*.md`) are English
- Do not commit temporary or scratch files at the package root

---

## 🌍 Language Note

Documentation inside the code (PHPDoc `/** ... */`) is written in **Bahasa Indonesia** to support local maintainers and interns. Feel free to contribute in English or Indonesian—both are welcome.

---

## 🧪 Testing

If you add new helpers or modules, please include basic PHPUnit tests or usage examples. We aim to make testing part of the educational starter kit.

---

## 📝 License

This project is licensed under the MIT License. By contributing, you agree that your code will be released under this license.

---

We appreciate every contribution—big or small. Terima kasih!