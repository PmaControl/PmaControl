---
title: How to Create Your Own Repositories for Packages
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-create-your-own-repositories-for-packages/
  post_id: 21184
source_author:
  name: Evgeniy Patlan
  slug: evgeniy-patlan
  url: https://www.percona.com/blog/author/evgeniy-patlan/
  website: ''
published_at: '2020-01-02T17:59:22'
published_at_gmt: '2020-01-02T17:59:22'
modified_at: '2026-05-05T17:23:39'
modified_at_gmt: '2026-05-05T17:23:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- MySQL
- Open Source
category_slugs:
- mysql
- open-source
tags:
- MySQL
- Open Source
tag_slugs:
- mysql
- open-source
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-to-Create-Your-Own-Repositories-for-Packages.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Create Your Own Repositories for Packages

Source: [Percona Blog](https://www.percona.com/blog/how-to-create-your-own-repositories-for-packages/)

Auteur source: [Evgeniy Patlan](https://www.percona.com/blog/author/evgeniy-patlan/)

Publication: 2020-01-02T17:59:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For Linux, the most common way to distribute software is binary packages in the rpm or deb format. Most packages are included in the official distribution repositories or 3rd party software repositories. Nevertheless, there are some cases where you need to install just a few standalone packages. You might be able to use the local … Continued

## Structure detectee

- H2: RPM-Based Distributions
- H4: 1. Install createrepo utility
- H4: 2. Create a repository directory
- H4: 3. Put RPM files into the repository directory
- H4: 4. Create the repository metadata
- H4: 5. Create the repository configuration file
- H2: Debian-Based Systems
- H4: 1. Install dpkg-dev utility
- H4: 2. Create a repository directory
- H4: 3. Put deb files into the repository directory
- H4: 4. Create a file that “apt-get update” can read
- H4: 5. Add info to your sources.list pointing at your repository

## Images et graphiques reperes

- featured / image: [How to Create Your Own Repositories for Packages](https://www.percona.com/wp-content/uploads/2026/03/How-to-Create-Your-Own-Repositories-for-Packages.png)
- content / image: [Optimize MySQL performance like a pro with Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/7c27dc64-d150-470f-8c0f-bb604c0ee660.png)
- content / image: [percona-package-320x259-transparent-300x243-1.png](https://www.percona.com/wp-content/uploads/2026/03/percona-package-320x259-transparent-300x243-1.png)
- content / image: [Fixing Data Slowdowns](https://www.percona.com/wp-content/uploads/2026/03/Fixing-Data-Slowdowns.png)

## Auteur source

Evgeniy joined Percona in April 2016. He is working as a Lead Release Engineer. Previously he was working for more than 8 years for PortaOne Inc. where he changed roles from support engineer to build/release engineer.
