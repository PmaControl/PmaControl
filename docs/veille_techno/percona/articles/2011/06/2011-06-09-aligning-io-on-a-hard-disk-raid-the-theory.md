---
title: Aligning IO on a hard disk RAID – the Theory
source:
  name: Percona Blog
  url: https://www.percona.com/blog/aligning-io-on-a-hard-disk-raid-the-theory/
  post_id: 2903
source_author:
  name: Aurimas Mikalauskas
  slug: inner
  url: https://www.percona.com/blog/author/inner/
  website: http://www.speedemy.com/
published_at: '2011-06-09T07:00:01'
published_at_gmt: '2011-06-09T07:00:01'
modified_at: '2026-04-28T21:27:13'
modified_at_gmt: '2026-04-28T21:27:13'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/fs-alignment.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Aligning IO on a hard disk RAID – the Theory

Source: [Percona Blog](https://www.percona.com/blog/aligning-io-on-a-hard-disk-raid-the-theory/)

Auteur source: [Aurimas Mikalauskas](https://www.percona.com/blog/author/inner/)

Publication: 2011-06-09T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Now that flash storage is becoming more popular, IO alignment question keeps popping up more often than it used to when all we had were rotating hard disk drives. I think the reason is very simple – when systems only had one bearing hard disk drive (HDD) as in RAID1 or one disk drive at … Continued

## Structure detectee

- H2: What is IO alignment
- H3: InnoDB page
- H3: File system
- H3: LVM
- H3: Partition table
- H3: RAID stripe
- H3: Disk sectors
- H2: Summary

## Images et graphiques reperes

- featured / image: [Aligning IO on a hard disk RAID – the Theory](https://www.percona.com/wp-content/uploads/2026/03/fs-alignment.png)
- content / image: [innodb-page-align-to-stripe.png](https://www.percona.com/wp-content/uploads/2026/03/innodb-page-align-to-stripe.png)
- content / image: [storage-stack.png](https://www.percona.com/wp-content/uploads/2026/03/storage-stack.png)

## Auteur source

Aurimas joined Percona as a first MySQL Performance Consultant in 2006, a few months after Peter and Vadim founded the company. His primary focus is on high performance, but he also specializes in full text search, high availability, content caching techniques and MySQL data recovery.
