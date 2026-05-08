---
title: Resolving Data Drift in a Dual-Primary Topology With Replica in MySQL/MariaDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/resolving-data-drift-in-a-dual-primary-topology-with-replica/
  post_id: 27447
source_author:
  name: Fernando Mattera
  slug: fernando-mattera
  url: https://www.percona.com/blog/author/fernando-mattera/
  website: ''
published_at: '2023-10-31T13:06:21'
published_at_gmt: '2023-10-31T13:06:21'
modified_at: '2026-03-26T20:27:03'
modified_at_gmt: '2026-03-26T20:27:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- data drift
- dual primary
- MariaDB
- MySQL
- mysql-and-variants
- Percona Toolkit
- Replication
tag_slugs:
- data-drift
- dual-primary
- mariadb
- mysql
- mysql-and-variants
- percona-toolkit
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_texture_of_computer_code_teal_and_navy_blue_colo_273ce901-924e-4690-9830-9f04aa37b6d0.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Resolving Data Drift in a Dual-Primary Topology With Replica in MySQL/MariaDB

Source: [Percona Blog](https://www.percona.com/blog/resolving-data-drift-in-a-dual-primary-topology-with-replica/)

Auteur source: [Fernando Mattera](https://www.percona.com/blog/author/fernando-mattera/)

Publication: 2023-10-31T13:06:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Hello friends, In Managed Services, we have the opportunity to see different technologies and various topologies, which makes the work fascinating and challenging at the same time. This time, I’m going to tell you about a particular case: a client with a dual-primary topology plus a replica, as detailed below: PS-primary-1=192.168.0.14 [RW]<br> |___ PS-primary-2=192.168.0.59 [RW] (Slave_delay: 0)<br> |___ PS-replica-1=192.168.0.99 [R] (Slave_delay: 0) 1 PS - primary - 1 = 192.168.0.14 [ RW ] < br > | ___ PS - primary - 2 = 192.168.0.59 [ RW ] ( Slave_delay : 0 ) < br > | ___ PS - replica - 1 = 192.168.0.99 [ R ] ( Slave_delay : 0 ) [RW] means Read/Write access. [R] means … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Resolving Data Drift in a Dual-Primary Topology With Replica in MySQL/MariaDB](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_a_texture_of_computer_code_teal_and_navy_blue_colo_273ce901-924e-4690-9830-9f04aa37b6d0.png)

## Auteur source

Fernando has worked as a DBA for more than 25 years in different technologies, he has worked in large telecommunication companies, technology, and his last job before joining Percona, in the largest online travel agency in Latin America. He likes soccer very much and is a fan of the team from his local city, Quilmes.
