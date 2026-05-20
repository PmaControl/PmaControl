---
title: High availability for MySQL on Amazon EC2 â€" Part 2 – Setting up the initial instances
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-2-setting-up-the-initial-instances/
  post_id: 2393
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2010-06-29T15:23:45'
published_at_gmt: '2010-06-29T15:23:45'
modified_at: '2026-04-28T21:14:51'
modified_at_gmt: '2026-04-28T21:14:51'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- High Availability
tag_slugs:
- high-availability
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# High availability for MySQL on Amazon EC2 â€" Part 2 – Setting up the initial instances

Source: [Percona Blog](https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-2-setting-up-the-initial-instances/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2010-06-29T15:23:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post is the second of a series that started here. The first step to build the HA solution is to create two working instances, configure them to be EBS based and create a security group for them. A third instance, the client, will be discussed in part 7. Since this will be a proof … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
