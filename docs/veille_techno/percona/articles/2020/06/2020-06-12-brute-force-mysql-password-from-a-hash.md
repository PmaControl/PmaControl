---
title: Brute-Force MySQL Password From a Hash
source:
  name: Percona Blog
  url: https://www.percona.com/blog/brute-force-mysql-password-from-a-hash/
  post_id: 22514
source_author:
  name: Mykola Marzhan
  slug: mykola-marzhan
  url: https://www.percona.com/blog/author/mykola-marzhan/
  website: ''
published_at: '2020-06-12T15:49:22'
published_at_gmt: '2020-06-12T15:49:22'
modified_at: '2026-05-05T17:30:56'
modified_at_gmt: '2026-05-05T17:30:56'
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
- Insight for DBAs
- MySQL
- Security
category_slugs:
- insight-for-dbas
- mysql
- security
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- security
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Brute-Force-MySQL-password.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Brute-Force MySQL Password From a Hash

Source: [Percona Blog](https://www.percona.com/blog/brute-force-mysql-password-from-a-hash/)

Auteur source: [Mykola Marzhan](https://www.percona.com/blog/author/mykola-marzhan/)

Publication: 2020-06-12T15:49:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In most cases, MySQL password instructions provide information on changing MySQL user passwords on the production system (e.g., reset root password without restart). It is even recommended to change passwords regularly for security reasons. But still, sometimes DBA duties on legacy systems offer surprises and you need to recover the original password for some old … Continued

## Structure detectee

- H2: Note on Security and mysql-unsha1 Attack
- H2: Dump Hash
- H2: Run Linode GPU Instance
- H2: Prepare Dictionary
- H2: Compile Hashcat
- H2: Enable OpenCL for NVIDIA
- H2: Run Password Recovery
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Brute-Force MySQL Password From a Hash](https://www.percona.com/wp-content/uploads/2026/03/Brute-Force-MySQL-password.png)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [mysql-performance-tuning-1.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)
- content / image: [Mysql-performance-tuning.png](https://www.percona.com/wp-content/uploads/2026/03/Mysql-performance-tuning.png)
