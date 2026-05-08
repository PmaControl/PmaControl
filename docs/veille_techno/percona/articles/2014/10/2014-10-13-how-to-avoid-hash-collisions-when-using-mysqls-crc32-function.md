---
title: How to avoid hash collisions when using MySQL’s CRC32 function
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-avoid-hash-collisions-when-using-mysqls-crc32-function/
  post_id: 8628
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2014-10-13T14:43:42'
published_at_gmt: '2014-10-13T14:43:42'
modified_at: '2026-05-05T16:55:16'
modified_at_gmt: '2026-05-05T16:55:16'
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
- tag:percona-toolkit:378
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Arunjith Aravindan
- checksum queries
- CRC32
- hash functions
- MD5
- Message-Digest algorithm 5
- MySQL tables
- Percona Toolkit
- Primary
- pt-table-checksum
- pt-table-sync
tag_slugs:
- arunjith-aravindan
- checksum-queries
- crc32
- hash-functions
- md5
- message-digest-algorithm-5
- mysql-tables
- percona-toolkit
- primary
- pt-table-checksum
- pt-table-sync
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to avoid hash collisions when using MySQL’s CRC32 function

Source: [Percona Blog](https://www.percona.com/blog/how-to-avoid-hash-collisions-when-using-mysqls-crc32-function/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2014-10-13T14:43:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Toolkit’s pt-table-checksum performs an online replication consistency check by executing checksum queries on the master, which produces different results on replicas that are inconsistent with the master – and the tool pt-table-sync synchronizes data efficiently between MySQL tables. The tools by default use the CRC32. Other good choices include MD5 and SHA1. If you … Continued

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
