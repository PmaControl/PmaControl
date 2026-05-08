---
title: 'Percona XtraDB Cluster: SElinux is not always the culprit !'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-selinux-is-not-always-the-culprit/
  post_id: 6498
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2012-12-20T14:02:30'
published_at_gmt: '2012-12-20T14:02:30'
modified_at: '2026-05-05T22:47:24'
modified_at_gmt: '2026-05-05T22:47:24'
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
category_slugs:
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster: SElinux is not always the culprit !

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-selinux-is-not-always-the-culprit/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2012-12-20T14:02:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you are using SElinux, you should know that it’s advised to disable it to avoid issue with PXC. Generally the communication between your nodes doesn’t work properly and a node having SElinux enabled won’t be able to join the cluster. So when a node doesn’t join the cluster where it should, my first reflex … Continued

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
