---
title: Live MySQL Slave Rebuild with Percona Toolkit
source:
  name: Percona Blog
  url: https://www.percona.com/blog/live-mysql-slave-rebuild-with-percona-toolkit/
  post_id: 20066
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2019-03-13T11:58:07'
published_at_gmt: '2019-03-13T11:58:07'
modified_at: '2026-05-05T16:20:09'
modified_at_gmt: '2026-05-05T16:20:09'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Data inconsistencies
- Data Recovery
- MySQL Data Recovery
- Replication
- schema change inconsistencies
tag_slugs:
- data-inconsistencies
- data-recovery
- mysql-data-recovery
- replication
- schema-change-inconsistencies
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-slave-data-out-of-sync.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Live MySQL Slave Rebuild with Percona Toolkit

Source: [Percona Blog](https://www.percona.com/blog/live-mysql-slave-rebuild-with-percona-toolkit/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2019-03-13T11:58:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, we had an edge case where a MySQL slave went out-of-sync but it couldn’t be rebuilt from scratch. The slave was acting as a master server to some applications and it had data was being written to it. It was a design error, and this is not recommended, but it happened. So how do … Continued

## Structure detectee

- H2: Scenario
- H2: Fixing the issue
- H3: Working past the errors
- H3: Finding the inconsistencies
- H3: Fixing the data inconsistencies
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Live MySQL Slave Rebuild with Percona Toolkit](https://www.percona.com/wp-content/uploads/2026/03/MySQL-slave-data-out-of-sync.jpg)
- content / image: [MySQL slave data out of sync](https://www.percona.com/wp-content/uploads/2026/03/MySQL-slave-data-out-of-sync-300x200.jpg)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
