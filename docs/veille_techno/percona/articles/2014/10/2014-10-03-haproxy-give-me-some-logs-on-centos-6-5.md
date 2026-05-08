---
title: 'HAProxy: Give me some logs on CentOS 6.5!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/haproxy-give-me-some-logs-on-centos-6-5/
  post_id: 8614
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2014-10-03T13:59:50'
published_at_gmt: '2014-10-03T13:59:50'
modified_at: '2026-04-28T22:11:41'
modified_at_gmt: '2026-04-28T22:11:41'
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
- CentOS 6.5
- galera
- haproxy
- Stephane Combaudon
tag_slugs:
- centos-6-5
- galera
- haproxy
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# HAProxy: Give me some logs on CentOS 6.5!

Source: [Percona Blog](https://www.percona.com/blog/haproxy-give-me-some-logs-on-centos-6-5/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2014-10-03T13:59:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

HAProxy is frequently used as a load-balancer in front of a Galera cluster. While diagnosing an issue with HAProxy configuration, I realized that logging doesn’t work out of the box on CentOS 6.5. Here is a simple recipe to fix the issue. If you look at the top of /etc/haproxy/haproxy.cfg, you will see something like: … Continued

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
