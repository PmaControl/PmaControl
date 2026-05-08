---
title: PMM Now Supports Monitoring of PostgreSQL Instances Connecting With Any Database (Name)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pmm-now-supports-monitoring-of-postgresql-instances-connecting-with-any-database-name/
  post_id: 25209
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2021-12-20T17:44:10'
published_at_gmt: '2021-12-20T17:44:10'
modified_at: '2026-03-26T20:09:11'
modified_at_gmt: '2026-03-26T20:09:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:pmm:2167
categories:
- Monitoring
- Percona Software
- PostgreSQL
category_slugs:
- monitoring
- percona-software
- postgresql
tags:
- PMM
- PostgreSQL
tag_slugs:
- pmm
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Monitoring-of-PostgreSQL-Instances.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PMM Now Supports Monitoring of PostgreSQL Instances Connecting With Any Database (Name)

Source: [Percona Blog](https://www.percona.com/blog/pmm-now-supports-monitoring-of-postgresql-instances-connecting-with-any-database-name/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2021-12-20T17:44:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The recent release of Percona Monitoring and Management 2.25.0 (PMM) includes a fix for bug PMM-6937: before that, PMM expected all monitoring connections to PostgreSQL servers to be made using the default postgres database. This worked well for most deployments, however, some DBaaS providers like Heroku and DigitalOcean do not provide direct access to the … Continued

## Structure detectee

- H2: Secure Connections
- H3: Web Interface
- H3: Command-Line Tool
- H2: Tracking Stats

## Images et graphiques reperes

- featured / image: [PMM Now Supports Monitoring of PostgreSQL Instances Connecting With Any Database (Name)](https://www.percona.com/wp-content/uploads/2026/03/Monitoring-of-PostgreSQL-Instances.png)
- content / image: [PMM TLS connections](https://www.percona.com/wp-content/uploads/2026/03/screenshot964.png)
- content / image: [PMM Skip TLS](https://www.percona.com/wp-content/uploads/2026/03/screenshot965.png)
- content / image: [pg_stat_statements](https://www.percona.com/wp-content/uploads/2026/03/screenshot958.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
