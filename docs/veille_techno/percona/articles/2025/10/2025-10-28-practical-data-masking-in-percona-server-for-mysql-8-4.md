---
title: Practical Data Masking in Percona Server for MySQL 8.4
source:
  name: Percona Blog
  url: https://www.percona.com/blog/practical-data-masking-in-percona-server-for-mysql-8-4/
  post_id: 35364
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2025-10-28T15:01:49'
published_at_gmt: '2025-10-28T15:01:49'
modified_at: '2026-03-26T20:25:14'
modified_at_gmt: '2026-03-26T20:25:14'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
- Percona Software
- Security
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
- security
tags:
- Data Masking
- MySQL
- mysql-and-variants
- Percona Server for MySQL
- privacy
- security
tag_slugs:
- data-masking
- mysql
- mysql-and-variants
- percona-server
- privacy
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Practical-Data-Masking-in-Percona-Server-for-MySQL-8.4.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Practical Data Masking in Percona Server for MySQL 8.4

Source: [Percona Blog](https://www.percona.com/blog/practical-data-masking-in-percona-server-for-mysql-8-4/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2025-10-28T15:01:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Data masking lets you hide sensitive fields (emails, credit-card numbers, job titles, etc.) while keeping data realistic for reporting, support, or testing. It is particularly useful when you collaborate with external entities and need to share your data for development reasons. You also need to protect your data and keep your customers’ privacy safe. Last … Continued

## Structure detectee

- H2: Install the data masking component
- H2: Example data: Create a table and insert some rows
- H2: Simple masking examples
- H2: Example: Mask email but keep the domain
- H2: Example: Mask credit card (PAN), leaving the last four digits
- H2: Example: Mask job title with synthetic dictionary terms
- H2: Create a view that shows masked data
- H2: Can we use stored procedure for masking data?
- H2: Dump/export masked data
- H2: Use mysqldump with proxysql
- H3: Additional tips & caveats
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Practical Data Masking in Percona Server for MySQL 8.4](https://www.percona.com/wp-content/uploads/2026/03/Practical-Data-Masking-in-Percona-Server-for-MySQL-8.4.jpg)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
