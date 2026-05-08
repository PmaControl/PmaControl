---
title: 'MySQL-python: Adding caching_sha2_password and TLSv1.2 Support'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-python-adding-caching_sha2_password-tlsv1-2-support/
  post_id: 20255
source_author:
  name: Ceri Williams
  slug: ceri-williams
  url: https://www.percona.com/blog/author/ceri-williams/
  website: https://www.percona.com
published_at: '2019-04-18T11:34:23'
published_at_gmt: '2019-04-18T11:34:23'
modified_at: '2026-04-27T21:14:19'
modified_at_gmt: '2026-04-27T21:14:19'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Security
category_slugs:
- insight-for-dbas
- mysql
- security
tags:
- Python
tag_slugs:
- python
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/python-not-connecting-to-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL-python: Adding caching_sha2_password and TLSv1.2 Support

Source: [Percona Blog](https://www.percona.com/blog/mysql-python-adding-caching_sha2_password-tlsv1-2-support/)

Auteur source: [Ceri Williams](https://www.percona.com/blog/author/ceri-williams/)

Publication: 2019-04-18T11:34:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Python 2 reaches EOL on 2020-01-01 and one of its commonly used third-party packages is MySQL-python. If you have not yet migrated away from both of these, since MySQL-python does not support Python 3, then you may have come across some issues if you are using more recent versions of MySQL and are enforcing a … Continued

## Structure detectee

- H3: Help! MySQL-python won’t connect to my MySQL 8.0 instance
- H4: Changing the user’s authentication plugin
- H4: Configuring SSL options
- H4: Forcing TLSv1.2 or later to further secure connections
- H3: Solution: Build a new RPM
- H4: Almost there… now force user authentication with the caching_sha2_password plugin

## Images et graphiques reperes

- featured / image: [MySQL-python: Adding caching_sha2_password and TLSv1.2 Support](https://www.percona.com/wp-content/uploads/2026/03/python-not-connecting-to-MySQL.jpg)

## Auteur source

Ceri is a Senior Technical Operations Engineer at Percona. He has previously worked in a variety of industries ranging from telecoms to skin care and online travel, nearly always with a database by his side for more than 10 years. Living in the Welsh Marches area of the UK, Ceri enjoys the rural life and beautiful countryside whenever possible.
