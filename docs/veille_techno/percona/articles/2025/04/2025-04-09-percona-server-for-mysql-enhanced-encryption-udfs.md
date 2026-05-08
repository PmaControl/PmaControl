---
title: 'Percona Server for MySQL: Enhanced Encryption UDFs'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-for-mysql-enhanced-encryption-udfs/
  post_id: 34771
source_author:
  name: Yura Sorokin
  slug: yura-sorokin
  url: https://www.percona.com/blog/author/yura-sorokin/
  website: ''
published_at: '2025-04-09T13:14:41'
published_at_gmt: '2025-04-09T13:14:41'
modified_at: '2026-03-26T20:25:38'
modified_at_gmt: '2026-03-26T20:25:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
- Security
category_slugs:
- mysql
- percona-software
- security
tags:
- encryption
- MySQL
- Percona Server for MySQL
tag_slugs:
- encryption
- mysql
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Encryption-UDFs.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server for MySQL: Enhanced Encryption UDFs

Source: [Percona Blog](https://www.percona.com/blog/percona-server-for-mysql-enhanced-encryption-udfs/)

Auteur source: [Yura Sorokin](https://www.percona.com/blog/author/yura-sorokin/)

Publication: 2025-04-09T13:14:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Percona Server for MySQL 8.0.41 / 8.4.4, we introduced several improvements in Encryption User-Defined Functions. Added support for RSAES-OAEP (OAEP) padding for RSA encrypt / decrypt operations. Added support for RSASSA-PSS (PSS) padding for RSA sign / verify operations. Added new encryption_udf.legacy_padding_scheme component system variable. Normalized character set support for all Encryption UDFs. PKCS1 … Continued

## Structure detectee

- H2: PKCS1 OAEP padding for RSA encrypt/decrypt operations
- H2: PKCS1 PSS padding for RSA sign / verify operations
- H2: encryption_udf.legacy_padding_scheme component system variable
- H2: Character set normalization for string parameters/return values
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Server for MySQL: Enhanced Encryption UDFs](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Encryption-UDFs.jpg)
- content / image: [mysql performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1.png)

## Auteur source

Yura is a Principal Software Engineer at Percona, mostly working on Percona Server Core. You might have heard of him as an author of "Compressed Columns with Dictionaries", "SEQUENCE_TABLE()" and "C++ UDF wrappers". Before joining in July 2015 he was leading a cloud file service backend dev team which was focusing on client-side encryption. He has 20+ years of software development experience, primarily in C++. Yura holds Master degree in Computer Science from National Technical University of Ukraine. He lives in Kyiv, Ukraine.
