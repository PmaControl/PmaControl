---
title: What if … MySQL’s Repeatable Reads Cause You to Lose Money?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-if-mysqls-repeatable-reads-cause-you-to-lose-money/
  post_id: 25097
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-11-09T14:23:11'
published_at_gmt: '2021-11-09T14:23:11'
modified_at: '2026-05-05T22:46:18'
modified_at_gmt: '2026-05-05T22:46:18'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- InnoDB
- isolation level
- locks
- MySQL
- mysql-and-variants
- Read comitted
- REPEATABLE READ
tag_slugs:
- innodb
- isolation-level
- locks
- mysql
- mysql-and-variants
- read-comitted
- repeatable-read
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Repeatable-Reads.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What if … MySQL’s Repeatable Reads Cause You to Lose Money?

Source: [Percona Blog](https://www.percona.com/blog/what-if-mysqls-repeatable-reads-cause-you-to-lose-money/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-11-09T14:23:11

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Well, let me say if that happens it’s because there is a logic mistake in your application. But you need to know and understand what happens in MySQL to be able to avoid the problem. In short, the WHY of this article is to inform you about possible pitfalls and how to prevent them from … Continued

## Structure detectee

- H2: The Scenario
- H2: The Run…
- H2: How Can I Prevent This From Happening?
- H3: Solution One
- H3: Solution Two
- H3: Solution Three
- H2: Conclusion
- H3: References

## Images et graphiques reperes

- featured / image: [What if … MySQL’s Repeatable Reads Cause You to Lose Money?](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Repeatable-Reads.png)
- content / image: [MySQL Repeatable Reads](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Repeatable-Reads-300x157.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
