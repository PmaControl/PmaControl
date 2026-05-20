---
title: MySQL 8.0.34 Improved Password Management by Defining the Change Characters Count
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-8-0-34-improved-password-management-by-defining-the-change-characters-count/
  post_id: 27379
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2023-08-10T12:24:08'
published_at_gmt: '2023-08-10T12:24:08'
modified_at: '2026-03-26T20:29:14'
modified_at_gmt: '2026-03-26T20:29:14'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
- Security
category_slugs:
- insight-for-dbas
- mysql
- security
tags:
- MySQL
- mysql-and-variants
- security
tag_slugs:
- mysql
- mysql-and-variants
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.34-Improved-Password-Management.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.0.34 Improved Password Management by Defining the Change Characters Count

Source: [Percona Blog](https://www.percona.com/blog/mysql-8-0-34-improved-password-management-by-defining-the-change-characters-count/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2023-08-10T12:24:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 8.0.34 brings us a new password validation parameter. Using this, we can control the minimum number of characters in a password that a user must change before validate_password accepts a new password for the user’s account.

## Structure detectee

- H2: Requirement
- H2: Creating a test environment
- H2: Testing “changed_characters_percentage”
- H2: How does it work with UPPER/LOWER case letters?
- H2: How does it work with different character counts?
- H4: More existing characters
- H4: More non-existing characters
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL 8.0.34 Improved Password Management by Defining the Change Characters Count](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8.0.34-Improved-Password-Management.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
