---
title: InnoDB Page Merging and Page Splitting
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-page-merging-and-page-splitting/
  post_id: 16673
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2017-04-10T19:08:01'
published_at_gmt: '2017-04-10T19:08:01'
modified_at: '2026-05-05T18:35:02'
modified_at_gmt: '2026-05-05T18:35:02'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- InnoDB
- Innodb internals
- Percona Server for MySQL
- space utilization
tag_slugs:
- innodb
- innodb-internals
- percona-server
- space-utilization
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Page-Merging-and-Page-Splitting-e1491850585819.png
image_count: 15
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Page Merging and Page Splitting

Source: [Percona Blog](https://www.percona.com/blog/innodb-page-merging-and-page-splitting/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2017-04-10T19:08:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you met one of the (few) MySQL consultants around the globe and asked him/her to review your queries and/or schemas, I am sure that he/she would tell you something regarding the importance of good primary key(s) design. Especially in the case of InnoDB, I’m sure they started to explain to you about index merges … Continued

## Structure detectee

- H2: File-Table Components
- H2: Roots, Branches, and Leaves
- H2: Page Internals
- H2: Page Merging
- H2: Page Splits
- H2: My Primary Key
- H2: Conclusion
- H2: Acknowledgments

## Images et graphiques reperes

- featured / image: [InnoDB Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Page-Merging-and-Page-Splitting-e1491850585819.png)
- content / image: [segment_extent-e1491345857803.png](https://www.percona.com/wp-content/uploads/2026/03/segment_extent-e1491345857803.png)
- content / image: [Bplustree-1024x471.png](https://www.percona.com/wp-content/uploads/2026/03/Bplustree-1024x471.png)
- content / image: [Locality_1.png](https://www.percona.com/wp-content/uploads/2026/03/Locality_1.png)
- content / image: [Locality_2.png](https://www.percona.com/wp-content/uploads/2026/03/Locality_2.png)
- content / image: [Locality_4.png](https://www.percona.com/wp-content/uploads/2026/03/Locality_4.png)
- content / image: [Locality_3.png](https://www.percona.com/wp-content/uploads/2026/03/Locality_3.png)
- content / image: [Locality_5.png](https://www.percona.com/wp-content/uploads/2026/03/Locality_5.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Locality_6.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Locality_7.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Locality_9.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Locality_8.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/Locality_10.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/split_1.png)
- content / image: [Page Merging and Page Splitting](https://www.percona.com/wp-content/uploads/2026/03/merges_1.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
