# Content model

`config/portfolio.php` is the local source for profile, projects, and articles. `PortfolioContent` exposes an explicit allowlist to views.

Projects require slug, name, summary, context, problem, role, responsibilities, approach, technology, challenges, security, outcome, lessons, repository state, confidentiality state, and featured state. Articles require slug, title, description, date, reading time, status, and body. Only `published` articles load publicly.

Do not store secrets or private facts in content files even if the repository class would strip them.
