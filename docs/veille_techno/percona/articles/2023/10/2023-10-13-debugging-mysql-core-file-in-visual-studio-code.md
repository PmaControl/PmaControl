---
title: Debugging MySQL Core File in Visual Studio Code
source:
  name: Percona Blog
  url: https://www.percona.com/blog/debugging-mysql-core-file-in-visual-studio-code/
  post_id: 27551
source_author:
  name: Jinyou Ma
  slug: jinyou-ma
  url: https://www.percona.com/blog/author/jinyou-ma/
  website: ''
published_at: '2023-10-13T14:46:57'
published_at_gmt: '2023-10-13T14:46:57'
modified_at: '2026-03-26T20:27:04'
modified_at_gmt: '2026-03-26T20:27:04'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- Debugging
- MySQL
- mysql-and-variants
- VS Code
tag_slugs:
- debugging
- mysql
- mysql-and-variants
- vs-code
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/abstract-digital-background-science-technology-networks-big-data-link-3d-rendering.jpg
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Debugging MySQL Core File in Visual Studio Code

Source: [Percona Blog](https://www.percona.com/blog/debugging-mysql-core-file-in-visual-studio-code/)

Auteur source: [Jinyou Ma](https://www.percona.com/blog/author/jinyou-ma/)

Publication: 2023-10-13T14:46:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Visual Studio Code (VS) supports memory dump debugging via C/C++ extension: https://code.visualstudio.com/docs/cpp/cpp-debug#_memory-dump-debugging. When MySQL generates a core file, the VS code simplifies the process of debugging. This blog will discuss how to debug the core file in VS code. Installing c/c++ extension We need to install the c/c++ extension. Here are the instructions for doing … Continued

## Structure detectee

- H2: Installing c/c++ extension
- H2: Finding the binary file of MySQL
- H2: Installing debug info
- H2: Downloading the source code
- H2: Creating launch.json file
- H2: Debugging the core file
- H2: Mapping source code
- H2: Exploring the core file
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Debugging MySQL Core File in Visual Studio Code](https://www.percona.com/wp-content/uploads/2026/03/abstract-digital-background-science-technology-networks-big-data-link-3d-rendering.jpg)
- content / image: [install c/c++ extension](https://www.percona.com/wp-content/uploads/2026/03/cpptools.png)
- content / image: [debuginfo_package-1024x94.png](https://www.percona.com/wp-content/uploads/2026/03/debuginfo_package-1024x94.png)
- content / image: [launch-1024x420.png](https://www.percona.com/wp-content/uploads/2026/03/launch-1024x420.png)
- content / image: [debugging_run-1024x438.png](https://www.percona.com/wp-content/uploads/2026/03/debugging_run-1024x438.png)
- content / image: [debugging_callstack-1024x614.png](https://www.percona.com/wp-content/uploads/2026/03/debugging_callstack-1024x614.png)
- content / image: [debugging_9760-1024x568.png](https://www.percona.com/wp-content/uploads/2026/03/debugging_9760-1024x568.png)
- content / image: [loading_source_error-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/loading_source_error-scaled.png)
- content / image: [mysql_crash-1024x614.png](https://www.percona.com/wp-content/uploads/2026/03/mysql_crash-1024x614.png)
