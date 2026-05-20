---
title: 'Finding a Regression in MySQL Source Code: A Case Study'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/finding-a-regression-in-mysql-source-code-a-case-study/
  post_id: 22883
source_author:
  name: Marcelo Altmann
  slug: marcelo-altmann
  url: https://www.percona.com/blog/author/marcelo-altmann/
  website: https://blog.marceloaltmann.com
published_at: '2020-08-06T18:12:30'
published_at_gmt: '2020-08-06T18:12:30'
modified_at: '2026-04-27T22:11:15'
modified_at_gmt: '2026-04-27T22:11:15'
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
- Bugs
- Debugging
- MySQL
- MySQL Troubleshooting
- mysql-and-variants
tag_slugs:
- bugs
- debugging
- mysql
- mysql-troubleshooting
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/finding-regression-in-mysql-source.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Finding a Regression in MySQL Source Code: A Case Study

Source: [Percona Blog](https://www.percona.com/blog/finding-a-regression-in-mysql-source-code-a-case-study/)

Auteur source: [Marcelo Altmann](https://www.percona.com/blog/author/marcelo-altmann/)

Publication: 2020-08-06T18:12:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

At the Percona engineering team, we often receive requests to analyze changes in MySQL/Percona Server for MySQL behavior from one version to another, either due to regression or a bug fix (when having to point out to a customer that commit X has fixed their issue and upgrading to a version including that fix will … Continued

## Structure detectee

- H2: Git Bisect
- H2: MySQL MTR
- H2: Case Study
- H3: Summary

## Images et graphiques reperes

- featured / image: [Finding a Regression in MySQL Source Code: A Case Study](https://www.percona.com/wp-content/uploads/2026/03/finding-regression-in-mysql-source.png)
- content / image: [finding regression in mysql source](https://www.percona.com/wp-content/uploads/2026/03/finding-regression-in-mysql-source-300x168.png)
- content / image: [git bisect step 1](https://www.percona.com/wp-content/uploads/2026/03/marcelo_altmann_bisect_blog_step_1-1024x481.png)
- content / image: [git bisect step 2](https://www.percona.com/wp-content/uploads/2026/03/marcelo_altmann_bisect_blog_step_2-1.png)
- content / image: [git bisect step 3](https://www.percona.com/wp-content/uploads/2026/03/marcelo_altmann_bisect_blog_step_3.png)
- content / image: [git bisect step 4](https://www.percona.com/wp-content/uploads/2026/03/marcelo_altmann_bisect_blog_step_4-1024x321.png)

## Auteur source

Marcelo Altmann is a C++ Software Engineer working on MySQL related products. At Percona he has also worked as a Senior Support Engineer and a Tech Lead of the Support Team. Prior to joining Percona , he worked as a MySQL DBA at Ireland's CCTLD, and worked as a DBA/PHP developer in Brazil. He also blogs about other MySQL related stuff at his personal blog.
