---
title: 'Percona XtraDB Cluster: Quorum and Availability of the cluster'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-quorum-availability-cluster/
  post_id: 9315
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2015-06-12T13:44:49'
published_at_gmt: '2015-06-12T13:44:49'
modified_at: '2026-04-28T22:20:53'
modified_at_gmt: '2026-04-28T22:20:53'
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
category_slugs:
- mysql
tags:
- High Availability
- MySQL
- Percona XtraDB Cluster
- Primary
- pxc
- queries
- Quorum
- Stephane Combaudon
tag_slugs:
- high-availability
- mysql
- percona-xtradb-cluster
- primary
- pxc
- queries
- quorum
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster: Quorum and Availability of the cluster

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-quorum-availability-cluster/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2015-06-12T13:44:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraDB Cluster (PXC) has become a popular option to provide high availability for MySQL servers. However many people are still having a hard time understanding what will happen to the cluster when one or several nodes leave the cluster (gracefully or ungracefully). This is what we will clarify in this post. Nodes leaving gracefully … Continued

## Structure detectee

- H2: Nodes leaving gracefully
- H2: Nodes becoming unreachable
- H2: Why does the remaining node stop processing queries?
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
