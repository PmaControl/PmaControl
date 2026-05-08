---
title: High availability for MySQL on Amazon EC2 â€" Part 4 – The instance restart script
source:
  name: Percona Blog
  url: https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-4-the-instance-restart-script/
  post_id: 2431
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2010-08-19T12:54:21'
published_at_gmt: '2010-08-19T12:54:21'
modified_at: '2026-04-28T21:16:28'
modified_at_gmt: '2026-04-28T21:16:28'
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

# High availability for MySQL on Amazon EC2 â€" Part 4 – The instance restart script

Source: [Percona Blog](https://www.percona.com/blog/high-availability-for-mysql-on-amazon-ec2-part-4-the-instance-restart-script/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2010-08-19T12:54:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post is the fourth of a series that started here. From the previous of this series, we now have resources configured but instead of starting MySQL, Pacemaker invokes a script to start (or restart) the EC2 instance running MySQL. This blog post describes the instance restart script. Remember, I am more a DBA than … Continued

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
