---
title: Adaptive Hash Index on AWS Aurora
source:
  name: Percona Blog
  url: https://www.percona.com/blog/adaptive-hash-index-on-aws-aurora/
  post_id: 20496
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2019-06-25T15:14:17'
published_at_gmt: '2019-06-25T15:14:17'
modified_at: '2026-03-20T22:41:50'
modified_at_gmt: '2026-03-20T22:41:50'
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
- Adaptive Hash Index in InnoDB
- Aurora
- AWS
- MySQL
tag_slugs:
- adaptive-hash-index-in-innodb
- aurora
- aws
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Adaptive-Hash-Index-on-AWS-Aurora.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Adaptive Hash Index on AWS Aurora

Source: [Percona Blog](https://www.percona.com/blog/adaptive-hash-index-on-aws-aurora/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2019-06-25T15:14:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I had a case where queries against Aurora Reader were 2-3 times slower than on the Writer node. In this blog post, we are going to discuss why. I am not going to go into the details of how Aurora works, as there are other blog posts discussing that. Here I am only going … Continued

## Structure detectee

- H3: The Problem
- H3: Let’s enable the adaptive hash index
- H3: Is this causing the performance difference?
- H3: Why is it disabled on the Reader?
- H3: Why can I change it in the parameter group?
- H3: Is this causing any performance problems for me?
- H3: But I want fast queries!
- H3: Query Cache
- H3: Does AWS know about this?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Adaptive Hash Index on AWS Aurora](https://www.percona.com/wp-content/uploads/2026/03/Adaptive-Hash-Index-on-AWS-Aurora.jpeg)
- content / image: [Adaptive Hash Index on AWS Aurora](https://www.percona.com/wp-content/uploads/2026/03/Adaptive-Hash-Index-on-AWS-Aurora-300x183.jpeg)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
