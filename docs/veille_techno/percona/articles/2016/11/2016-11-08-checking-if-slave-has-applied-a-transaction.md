---
title: Checking if a Slave Has Applied a Transaction from the Master
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checking-if-slave-has-applied-a-transaction/
  post_id: 15942
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2016-11-08T21:08:52'
published_at_gmt: '2016-11-08T21:08:52'
modified_at: '2026-05-05T17:54:10'
modified_at_gmt: '2026-05-05T17:54:10'
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
- MySQL
- Replication
- slave
tag_slugs:
- mysql
- replication
- slave
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking if a Slave Has Applied a Transaction from the Master

Source: [Percona Blog](https://www.percona.com/blog/checking-if-slave-has-applied-a-transaction/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2016-11-08T21:08:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will discuss how we can verify if an application transaction executed on the master has been applied to the slaves. In summary, is a good practice to alleviate the load on the master by doing reads on slaves. It is acceptable in most of the cases to just connect on … Continued

## Structure detectee

- H4: Conclusion

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
