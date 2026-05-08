---
title: 'Webinar: Writing Application Code for MySQL High Availability Followup Questions'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/webinar-writing-application-code-mysql-high-availability-followup-questions/
  post_id: 10005
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-09-14T17:57:19'
published_at_gmt: '2015-09-14T17:57:19'
modified_at: '2026-05-05T22:26:06'
modified_at_gmt: '2026-05-05T22:26:06'
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
- MySQL
tag_slugs:
- high-availability
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/avail.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Webinar: Writing Application Code for MySQL High Availability Followup Questions

Source: [Percona Blog](https://www.percona.com/blog/webinar-writing-application-code-mysql-high-availability-followup-questions/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-09-14T17:57:19

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thanks to all who attended my webinar last week on Writing Application Code for MySQL High Availability. This blog is for me to address the extra questions I didn’t have time to answer on the stream. What do you think about using Galera Cluster but writing to a single Node with LVS ? Whatever HA … Continued

## Structure detectee

- H2: What do you think about using Galera Cluster but writing to a single Node with LVS ?
- H2: Is there any way we can determine slave lag and then decide to use weather master or slave? for e.g. instead of using query to find if data is available in slave then …. use if lag_time < xyz?
- H2: Error handling in JavaScript running in browser Or in webservice on server?
- H2: I guess with GO, you have lot of options to do like, putting thread in wait mode or spanning another thread. But with other languages like java, readability of code is not that Great

## Images et graphiques reperes

- featured / image: [Webinar: Writing Application Code for MySQL High Availability Followup Questions](https://www.percona.com/wp-content/uploads/2026/03/avail.png)

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
