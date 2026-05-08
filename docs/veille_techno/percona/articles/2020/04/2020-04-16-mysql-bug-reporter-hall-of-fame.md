---
title: MySQL Bug Reporter Hall of Fame
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-bug-reporter-hall-of-fame/
  post_id: 22242
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-04-16T17:05:59'
published_at_gmt: '2020-04-16T17:05:59'
modified_at: '2026-04-27T21:37:35'
modified_at_gmt: '2026-04-27T21:37:35'
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
- MySQL bugs
tag_slugs:
- mysql
- mysql-bugs
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Bug-Reporter-Hall-of-Fame.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Bug Reporter Hall of Fame

Source: [Percona Blog](https://www.percona.com/blog/mysql-bug-reporter-hall-of-fame/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-04-16T17:05:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I got access to the list of MySQL bug reports from bugs.mysql.com which someone crawled and stored in a MySQL database. I thought it would be interesting to see who the heroes are of MySQL bug reporting! Top MySQL Bug Reporters Ever Shell select rank() over(order by count(*) desc) my_rank, count(*) cnt, reporter from bugs where reporter != "OCA Admin" and reporter != "[ name withheld ]" group by reporter order by cnt desc limit 20; +---------+------+--------------------+ | my_rank | cnt | reporter | +---------+------+--------------------+ | 1 | 1234 | Shane Bester | | 2 | 869 | Peter Gulutzan | | 3 | 818 | Daniël van Eeden | | 4 | 587 | Joerg Bruehe | | 5 | 572 | Philip Stoev | | 6 | 568 | Peter Laursen | | 7 | 564 | Roel Van de Paar | | 8 | 526 | Guilhem Bichot | | 9 | 524 | Jonathan Miller | | 10 | 476 | Hartmut Holzgraefe | | 11 | 431 | Simon Mudd | | 12 | 389 | Matthias...

## Structure detectee

- H2: Top MySQL Bug Reporters Ever

## Images et graphiques reperes

- featured / image: [MySQL Bug Reporter Hall of Fame](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Bug-Reporter-Hall-of-Fame.png)
- content / image: [MySQL Bug Reporter Hall of Fame](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Bug-Reporter-Hall-of-Fame-300x168.png)
- content / image: [Screen-Shot-2020-04-16-at-11.15.22-AM-1024x619.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-16-at-11.15.22-AM-1024x619.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
