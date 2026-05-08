---
title: EC2/EBS single and RAID volumes IO benchmark
source:
  name: Percona Blog
  url: https://www.percona.com/blog/ec2ebs-single-and-raid-volumes-io-bencmark/
  post_id: 1961
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-08-07T02:30:21'
published_at_gmt: '2009-08-07T02:30:21'
modified_at: '2026-05-04T19:41:13'
modified_at_gmt: '2026-05-04T19:41:13'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- XtraBackup
matched_filters:
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Benchmarks
category_slugs:
- benchmarks
tags:
- benchmark
- cloud
- ec2
- io
- raid
tag_slugs:
- benchmark
- cloud
- ec2
- io
- raid
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/rndrd.png
image_count: 3
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# EC2/EBS single and RAID volumes IO benchmark

Source: [Percona Blog](https://www.percona.com/blog/ec2ebs-single-and-raid-volumes-io-bencmark/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-08-07T02:30:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

During preparation of Percona-XtraDB template to run in RightScale environment, I noticed that IO performance on EBS volume in EC2 cloud is not quite perfect. So I have spent some time benchmarking volumes. Interesting part with EBS volumes is that you see it as device in your OS, so you can easily make software RAID … Continued

## Images et graphiques reperes

- featured / graph_or_chart: [EC2/EBS single and RAID volumes IO benchmark](https://www.percona.com/wp-content/uploads/2026/03/rndrd.png)
- content / image: [random write](https://www.percona.com/wp-content/uploads/2026/03/rndwr.png)
- content / image: [random read-write](https://www.percona.com/wp-content/uploads/2026/03/rndrw.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
