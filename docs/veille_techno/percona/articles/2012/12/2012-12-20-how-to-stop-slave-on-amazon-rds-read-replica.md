---
title: How to STOP SLAVE on Amazon RDS read replica
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-stop-slave-on-amazon-rds-read-replica/
  post_id: 6499
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2012-12-20T12:21:43'
published_at_gmt: '2012-12-20T12:21:43'
modified_at: '2026-04-28T21:42:58'
modified_at_gmt: '2026-04-28T21:42:58'
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
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- amazon
- AWS
- ec2
- MySQL
- RDS
- Tips
tag_slugs:
- amazon
- aws
- ec2
- mysql
- rds
- tips
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to STOP SLAVE on Amazon RDS read replica

Source: [Percona Blog](https://www.percona.com/blog/how-to-stop-slave-on-amazon-rds-read-replica/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2012-12-20T12:21:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We are doing a migration from Amazon RDS to EC2 with a customer. This, unfortunately, involves some downtime – if you are an RDS user, you probably know you can’t replicate an RDS instance to an external server (or even EC2). While it is annoying, this post isn’t going to be a rant on how … Continued

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
