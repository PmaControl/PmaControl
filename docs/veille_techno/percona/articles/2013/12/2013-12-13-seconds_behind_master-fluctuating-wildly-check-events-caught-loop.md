---
title: Seconds_Behind_Master fluctuating wildly? Check for events caught in a loop
source:
  name: Percona Blog
  url: https://www.percona.com/blog/seconds_behind_master-fluctuating-wildly-check-events-caught-loop/
  post_id: 7537
source_author:
  name: Michael Coburn
  slug: michael-coburn
  url: https://www.percona.com/blog/author/michael-coburn/
  website: ''
published_at: '2013-12-13T08:00:40'
published_at_gmt: '2013-12-13T08:00:40'
modified_at: '2026-03-25T17:14:10'
modified_at_gmt: '2026-03-25T17:14:10'
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
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- Seconds_Behind_Master
tag_slugs:
- seconds_behind_master
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Seconds_Behind_Master fluctuating wildly? Check for events caught in a loop

Source: [Percona Blog](https://www.percona.com/blog/seconds_behind_master-fluctuating-wildly-check-events-caught-loop/)

Auteur source: [Michael Coburn](https://www.percona.com/blog/author/michael-coburn/)

Publication: 2013-12-13T08:00:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I was working with a customer where we noticed that Seconds_Behind_Master fluctuating from an expected value of 0 seconds behind to a fairly high six figure value. The servers were configured in a master-master relationship and used 5 figure server_id values, and we had just migrated this cluster from one data centre to another … Continued

## Auteur source

Michael Coburn works at Percona on the Professional Services team in the role of Principal Architect. Michael joined Percona in 2012 as a Consultant after having worked as a DBA with stock photography websites and email service provider platforms. With a foundation in Systems Administration, Michael previously served as Product Manager responsible for Percona Monitoring and Management (PMM).
