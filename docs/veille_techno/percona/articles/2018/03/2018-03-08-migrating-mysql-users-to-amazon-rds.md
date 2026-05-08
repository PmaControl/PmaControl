---
title: Migrating MySQL Users to Amazon RDS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-mysql-users-to-amazon-rds/
  post_id: 18285
source_author:
  name: Alok Pathak
  slug: alok-pathak
  url: https://www.percona.com/blog/author/alok-pathak/
  website: ''
published_at: '2018-03-08T19:49:20'
published_at_gmt: '2018-03-08T19:49:20'
modified_at: '2026-05-05T19:07:41'
modified_at_gmt: '2026-05-05T19:07:41'
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
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-MySQL-Users-to-Amazon-RDS.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating MySQL Users to Amazon RDS

Source: [Percona Blog](https://www.percona.com/blog/migrating-mysql-users-to-amazon-rds/)

Auteur source: [Alok Pathak](https://www.percona.com/blog/author/alok-pathak/)

Publication: 2018-03-08T19:49:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at what is needed when migrating MySQL users to Amazon RDS. We’ll discuss how we can transform MySQL user grants and make them compatible with Amazon RDS. In order to deliver a managed service experience, Amazon RDS does not provide shell access to the underlying operating system. It also … Continued

## Structure detectee

- H4: Identify users having privileges that aren’t supported by RDS
- H4: Export grants using pt-show-grants
- H4: Import users in a separate MySQL instance running the same version
- H4: Remove the forbidden privileges using the REVOKE statement
- H4: Export grants again using pt-show-grants and load them to RDS
- H4: Conclusion

## Images et graphiques reperes

- featured / image: [Migrating MySQL Users to Amazon RDS](https://www.percona.com/wp-content/uploads/2026/03/Migrating-MySQL-Users-to-Amazon-RDS.jpg)
- content / image: [Migrating MySQL Users to Amazon RDS](https://www.percona.com/wp-content/uploads/2026/03/Migrating-MySQL-Users-to-Amazon-RDS-300x222.jpg)

## Auteur source

Alok joined Percona in May 2014 as a Consultant. He is currently working as Senior MySQL DBA in Managed Services. He has previously worked as MySQL DBA for various product and service based companies in India. His career interests include Performance Tuning, Query Optimization, High Availability, Database Clustering and Automation. When not working, he enjoys listening to music and watching sports.
