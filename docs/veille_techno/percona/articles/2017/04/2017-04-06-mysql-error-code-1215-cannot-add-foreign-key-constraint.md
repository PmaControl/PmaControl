---
title: 'MySQL Error Code 1215: "Cannot add foreign key constraint"'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-error-code-1215-cannot-add-foreign-key-constraint/
  post_id: 16636
source_author:
  name: marcos.albe
  slug: marcos-albe
  url: https://www.percona.com/blog/author/marcos-albe/
  website: http://www.percona.com
published_at: '2017-04-06T18:11:38'
published_at_gmt: '2017-04-06T18:11:38'
modified_at: '2026-04-16T16:18:17'
modified_at_gmt: '2026-04-16T16:18:17'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Diablo Technologies
- error code
- MySQL
tag_slugs:
- diablo-technologies
- error-code
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Error Code 1215: "Cannot add foreign key constraint"

Source: [Percona Blog](https://www.percona.com/blog/mysql-error-code-1215-cannot-add-foreign-key-constraint/)

Auteur source: [marcos.albe](https://www.percona.com/blog/author/marcos-albe/)

Publication: 2017-04-06T18:11:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Updated 7-05-2019 In this blog, we’ll look at how to resolve MySQL error code 1215: “Cannot add foreign key constraint”. This error often appears with little context: MySQL ERROR 1215 (HY000): Cannot add foreign key constraint 1 ERROR 1215 (HY000): Cannot add foreign key constraint There are many possible causes. This guide covers the most common ones, how to diagnose them, and how to fix them. Tip: Start by checking SHOW ENGINE … Continued

## Structure detectee

- H3: 1) Referenced table does not exist
- H3: 2) Incorrect quoting
- H3: 3) Typos in table or column names
- H3: 4) Column type mismatch
- H3: 5) Referenced column is not indexed
- H3: 6) Referenced column not leftmost in composite index
- H3: 7) Charset/collation mismatch
- H3: 8) Parent table not using InnoDB
- H3: 9) Missing column in REFERENCES
- H3: 10) Parent table is partitioned
- H3: 11) Referencing a virtual/generated column
- H3: 12) Using SET DEFAULT
- H3: 13) SET NULL on NOT NULL column
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [MySQL Error Code 1215: "Cannot add foreign key constraint"](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-Troubleshooting-e1480963086227.jpg)
- content / image: [Get 24/7 Database Support for MySQL today!](https://www.percona.com/wp-content/uploads/2026/03/f872d889-3ab9-4249-9573-10f5b3b2bed0.png)
