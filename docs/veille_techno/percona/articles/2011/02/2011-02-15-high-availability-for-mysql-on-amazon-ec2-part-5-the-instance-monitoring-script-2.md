---
title: High availability for MySQL on Amazon EC2 â€" Part 5 – The instance monitoring script
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-5-the-instance-monitoring-script-2/
  post_id: 2699
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2011-02-15T18:44:10'
published_at_gmt: '2011-02-15T18:44:10'
modified_at: '2026-04-28T21:23:16'
modified_at_gmt: '2026-04-28T21:23:16'
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
- MySQL
category_slugs:
- cloud
- mysql
tags:
- amazon
- High Availability
tag_slugs:
- amazon
- high-availability
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High availability for MySQL on Amazon EC2 â€" Part 5 – The instance monitoring script

Source: [Percona Blog](https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-5-the-instance-monitoring-script-2/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2011-02-15T18:44:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post is the fifth of a series that started here. From the previous posts of this series, we now have nearly everything setup, only a few pieces are missing. One of the missing pieces is the Pacemaker script that run on the MySQL instance.

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
