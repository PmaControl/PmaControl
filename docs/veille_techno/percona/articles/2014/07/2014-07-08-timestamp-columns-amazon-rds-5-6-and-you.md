---
title: TIMESTAMP Columns, Amazon RDS 5.6, and You
source:
  name: Percona Blog
  url: https://www.percona.com/blog/timestamp-columns-amazon-rds-5-6-and-you/
  post_id: 8349
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2014-07-08T14:18:01'
published_at_gmt: '2014-07-08T14:18:01'
modified_at: '2026-04-28T22:07:09'
modified_at_gmt: '2026-04-28T22:07:09'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- '5.6'
- amazon
- Amazon RDS
- default variable values
- Ernie Souhrada
- MySQL 5.6
- temporal data types
tag_slugs:
- 5-6
- amazon
- amazon-rds
- default-variable-values
- ernie-souhrada
- mysql-5-6
- temporal-data-types
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# TIMESTAMP Columns, Amazon RDS 5.6, and You

Source: [Percona Blog](https://www.percona.com/blog/timestamp-columns-amazon-rds-5-6-and-you/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2014-07-08T14:18:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This comes from an issue that I worked on recently, wherein a customer reported that their application was working fine under stock MySQL 5.6 but producing erroneous results when they tried running it on Amazon RDS 5.6. They had a table which, on the working server, contained two TIMESTAMP columns, one which defaulted to CURRENT_TIMESTAMP … Continued

## Structure detectee

- H4: So, what have we learned here?

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
