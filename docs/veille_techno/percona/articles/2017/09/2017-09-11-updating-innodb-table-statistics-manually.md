---
title: How to Update InnoDB Table Statistics Manually
source:
  name: Percona Blog
  url: https://www.percona.com/blog/updating-innodb-table-statistics-manually/
  post_id: 17346
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-09-11T19:00:42'
published_at_gmt: '2017-09-11T19:00:42'
modified_at: '2026-05-05T19:25:42'
modified_at_gmt: '2026-05-05T19:25:42'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- Insight for Developers
- Monitoring
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- monitoring
- mysql
tags:
- InnoDB
- InnoDB tables
- Monitoring
- MySQL
- Statistics
tag_slugs:
- innodb
- innodb-tables
- monitoring
- mysql
- statistics
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Tables.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Update InnoDB Table Statistics Manually

Source: [Percona Blog](https://www.percona.com/blog/updating-innodb-table-statistics-manually/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-09-11T19:00:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we will discuss the best way to update an InnoDB table manually. As a support engineer, I often see situations when the cardinality of a table is not correct. When InnoDB calculates the cardinality of an index, it does not scan the full table by default. Instead it looks at random pages, … Continued

## Structure detectee

- H2: Update InnoDB Table Manually
- H2: Now let’s try our manual statistics update trick
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [How to Update InnoDB Table Statistics Manually](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Tables.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
