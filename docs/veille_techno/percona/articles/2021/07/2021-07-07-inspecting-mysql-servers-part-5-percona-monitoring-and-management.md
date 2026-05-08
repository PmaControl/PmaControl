---
title: 'Inspecting MySQL Servers Part 5: Percona Monitoring and Management'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/inspecting-mysql-servers-part-5-percona-monitoring-and-management/
  post_id: 24613
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2021-07-07T12:00:34'
published_at_gmt: '2021-07-07T12:00:34'
modified_at: '2026-03-23T18:33:39'
modified_at_gmt: '2026-03-23T18:33:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
- tag:pmm:2167
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- Monitoring
- MySQL
- mysql-and-variants
- Percona Software
- PMM
tag_slugs:
- monitoring
- mysql
- mysql-and-variants
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Inspecting-MySQL-Servers-PMM.png
image_count: 23
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Inspecting MySQL Servers Part 5: Percona Monitoring and Management

Source: [Percona Blog](https://www.percona.com/blog/inspecting-mysql-servers-part-5-percona-monitoring-and-management/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2021-07-07T12:00:34

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the previous posts of this series, I presented how the Percona Support team approaches the analysis and troubleshooting of a MySQL server using a tried-and-tested method supported by specific tools found in the Percona Toolkit: Inspecting MySQL Servers Part 1: The Percona Support Way Inspecting MySQL Servers Part 2: Knowing the Server Inspecting MySQL … Continued

## Structure detectee

- H2: Know the Server
- H2: What MySQL?
- H2: An Engine in Motion
- H2: QAN: Query Analytics
- H2: What PMM Does Not Include
- H2: Tuning the Engine for the Race Track

## Images et graphiques reperes

- featured / image: [Inspecting MySQL Servers Part 5: Percona Monitoring and Management](https://www.percona.com/wp-content/uploads/2026/03/Inspecting-MySQL-Servers-PMM.png)
- content / image: [Inspecting MySQL Servers PMM](https://www.percona.com/wp-content/uploads/2026/03/Inspecting-MySQL-Servers-PMM-300x169.png)
- content / graph_or_chart: [PMM Dashboard](https://www.percona.com/wp-content/uploads/2026/03/image15.png)
- content / image: [MySQL Node Summary](https://www.percona.com/wp-content/uploads/2026/03/image3-10-1024x453.png)
- content / image: [CPU utilization](https://www.percona.com/wp-content/uploads/2026/03/image4-11.png)
- content / image: [image20.png](https://www.percona.com/wp-content/uploads/2026/03/image20.png)
- content / image: [image14-1.png](https://www.percona.com/wp-content/uploads/2026/03/image14-1.png)
- content / image: [image7-6.png](https://www.percona.com/wp-content/uploads/2026/03/image7-6.png)
- content / image: [MySQL Summary](https://www.percona.com/wp-content/uploads/2026/03/image1-13.png)
- content / image: [image13-2.png](https://www.percona.com/wp-content/uploads/2026/03/image13-2.png)
- content / image: [MySQL Table Open Cache Status](https://www.percona.com/wp-content/uploads/2026/03/image21.png)
- content / image: [image16-1.png](https://www.percona.com/wp-content/uploads/2026/03/image16-1.png)
- content / image: [image2-12.png](https://www.percona.com/wp-content/uploads/2026/03/image2-12.png)
- content / image: [image10-1.png](https://www.percona.com/wp-content/uploads/2026/03/image10-1.png)
- content / image: [InnoDB Buffer Pool Requests](https://www.percona.com/wp-content/uploads/2026/03/image12-2.png)
- content / image: [image17-2.png](https://www.percona.com/wp-content/uploads/2026/03/image17-2.png)
- content / image: [image8-2.png](https://www.percona.com/wp-content/uploads/2026/03/image8-2.png)
- content / image: [image18-1.png](https://www.percona.com/wp-content/uploads/2026/03/image18-1.png)
- content / image: [image6-7.png](https://www.percona.com/wp-content/uploads/2026/03/image6-7.png)
- content / image: [image19-1.png](https://www.percona.com/wp-content/uploads/2026/03/image19-1.png)
- content / image: [PMM Query Analytics](https://www.percona.com/wp-content/uploads/2026/03/image11-1.png)
- content / image: [image9-1.png](https://www.percona.com/wp-content/uploads/2026/03/image9-1.png)
- content / image: [image5-9.png](https://www.percona.com/wp-content/uploads/2026/03/image5-9.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
