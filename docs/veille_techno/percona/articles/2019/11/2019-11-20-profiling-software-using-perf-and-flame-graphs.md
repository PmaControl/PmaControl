---
title: Profiling Software Using perf and Flame Graphs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/profiling-software-using-perf-and-flame-graphs/
  post_id: 21208
source_author:
  name: Agustín
  slug: agustin-gallego
  url: https://www.percona.com/blog/author/agustin-gallego/
  website: ''
published_at: '2019-11-20T18:47:10'
published_at_gmt: '2019-11-20T18:47:10'
modified_at: '2026-04-27T21:26:00'
modified_at_gmt: '2026-04-27T21:26:00'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- Percona Software
tag_slugs:
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Profiling-Software-Using-perf-and-Flame-Graphs.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Profiling Software Using perf and Flame Graphs

Source: [Percona Blog](https://www.percona.com/blog/profiling-software-using-perf-and-flame-graphs/)

Auteur source: [Agustín](https://www.percona.com/blog/author/agustin-gallego/)

Publication: 2019-11-20T18:47:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will see how to use perf (a.k.a.: perf_events) together with Flame Graphs. They are used to generate a graphical representation of what functions are being called within our software of choice. Percona Server for MySQL is used here, but it can be extended to any software you can take a … Continued

## Structure detectee

- H2: Installing Packages Needed
- H2: Capturing Samples
- H2: Preparing the Samples
- H2: Generating the Flame Graphs
- H2: How Does it Look?
- H3: Conclusion
- H3: Related links

## Images et graphiques reperes

- featured / image: [Profiling Software Using perf and Flame Graphs](https://www.percona.com/wp-content/uploads/2026/03/Profiling-Software-Using-perf-and-Flame-Graphs.png)
- content / image: [perf and Flame Graphs](https://www.percona.com/wp-content/uploads/2026/03/flamegraph1-scaled.png)
- content / image: [flame graphs](https://www.percona.com/wp-content/uploads/2026/03/flamegraph-2-scaled.png)

## Auteur source

Agustín joined Percona's Support team in December 2013, after being part of the Administrative team from February 2012. He has previously worked as a Cambridge IT examinations Supervisor and as a Junior BI, SQL & C# developer. He is studying to get a Computer Systems Engineer degree at the Universidad de la República, in Uruguay.
