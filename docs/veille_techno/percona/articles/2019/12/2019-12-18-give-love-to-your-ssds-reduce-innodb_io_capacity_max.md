---
title: Give Love to Your SSDs – Reduce innodb_io_capacity_max!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/give-love-to-your-ssds-reduce-innodb_io_capacity_max/
  post_id: 21308
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2019-12-18T18:17:20'
published_at_gmt: '2019-12-18T18:17:20'
modified_at: '2026-04-27T21:26:20'
modified_at_gmt: '2026-04-27T21:26:20'
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
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- storage-engine
tags:
- DBA
- MySQL
- Storage
tag_slugs:
- dba
- mysql
- storage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/reduce-innodb-io-capacity-max.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Give Love to Your SSDs – Reduce innodb_io_capacity_max!

Source: [Percona Blog](https://www.percona.com/blog/give-love-to-your-ssds-reduce-innodb_io_capacity_max/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2019-12-18T18:17:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The innodb_io_capacity and innodb_io_capacity_max are often misunderstood InnoDB parameters. As consultants, we see, at least every month, people setting this variable based on the top IO write specifications of their storage. Is this a correct choice? Is it an optimal value for performance? What about the SSD/Flash wear leveling? Innodb_io_capacity 101 Let’s begin with what … Continued

## Structure detectee

- H2: Innodb_io_capacity 101
- H2: Are Dirty Pages Evil?
- H2: Impacts of Excessive Flushing on Performance
- H2: SSD/Flash Wear Leveling
- H3: The Impact of the Filling Factor
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Give Love to Your SSDs – Reduce innodb_io_capacity_max!](https://www.percona.com/wp-content/uploads/2026/03/reduce-innodb-io-capacity-max.png)
- content / image: [reduce innodb io capacity max](https://www.percona.com/wp-content/uploads/2026/03/reduce-innodb-io-capacity-max-300x168.png)
- content / image: [Variation of Innodb_io_capacity, impact on idle flushing](https://www.percona.com/wp-content/uploads/2026/03/innodb_io_capacity_with_labels.png)
  Caption: Impacts of innodb_io_capacity on idle flushing
- content / image: [Updates per page flushed](https://www.percona.com/wp-content/uploads/2026/03/UpdatePerPageFlushedIOCapMax.png)
- content / image: [Write bandwidth needed to burn a SSD](https://www.percona.com/wp-content/uploads/2026/03/BandwidthToBurn.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
