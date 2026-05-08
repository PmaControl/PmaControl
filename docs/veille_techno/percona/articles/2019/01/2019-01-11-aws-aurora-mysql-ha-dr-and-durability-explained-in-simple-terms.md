---
title: AWS Aurora MySQL – HA, DR, and Durability Explained in Simple Terms
source:
  name: Percona Blog
  url: https://www.percona.com/blog/aws-aurora-mysql-ha-dr-and-durability-explained-in-simple-terms/
  post_id: 19740
source_author:
  name: Brian Walters
  slug: brian-walters
  url: https://www.percona.com/blog/author/brian-walters/
  website: ''
published_at: '2019-01-11T19:53:28'
published_at_gmt: '2019-01-11T19:53:28'
modified_at: '2026-05-05T20:37:58'
modified_at_gmt: '2026-05-05T20:37:58'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Aurora6.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# AWS Aurora MySQL – HA, DR, and Durability Explained in Simple Terms

Source: [Percona Blog](https://www.percona.com/blog/aws-aurora-mysql-ha-dr-and-durability-explained-in-simple-terms/)

Auteur source: [Brian Walters](https://www.percona.com/blog/author/brian-walters/)

Publication: 2019-01-11T19:53:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s a few weeks after AWS re:Invent 2018 and my head is still spinning from all of the information released at this year’s conference. This year I was able to enjoy a few sessions focused on Aurora deep dives. In fact, I walked away from the conference realizing that my own understanding of High Availability … Continued

## Structure detectee

- H3: Aurora MySQL – What is it?
- H3: Aurora Storage
- H3: Durable by Default
- H3: HA and DR Options
- H3: Single-AZ, Single Instance Deployment
- H3: Single-AZ, Multi-Instance
- H3: Multi-AZ Options
- H3: Cross-Region Options
- H4: Logical Replication
- H4: Physical Replication
- H3: Multi-Master Options
- H3: Summary
- H3: For More Information See Also:

## Images et graphiques reperes

- featured / image: [AWS Aurora MySQL – HA, DR, and Durability Explained in Simple Terms](https://www.percona.com/wp-content/uploads/2026/03/Aurora6.png)
- content / image: [introducing the aurora storage engine 1](https://www.percona.com/wp-content/uploads/2026/03/Aurora1-300x212.png)
- content / image: [great durability with Aurora but DA and HA less so](https://www.percona.com/wp-content/uploads/2026/03/Aurora2-300x156.png)
- content / image: [Aurora3-1024x249.png](https://www.percona.com/wp-content/uploads/2026/03/Aurora3-1024x249.png)
- content / image: [Introducing HA into an Amazon Aurora solution](https://www.percona.com/wp-content/uploads/2026/03/Aurora4-300x158.png)
- content / image: [Aurora5-1024x287.png](https://www.percona.com/wp-content/uploads/2026/03/Aurora5-1024x287.png)
- content / image: [Partial disaster recovery with Amazon aurora](https://www.percona.com/wp-content/uploads/2026/03/Aurora6-300x157.png)
- content / image: [Aurora7-1024x402.png](https://www.percona.com/wp-content/uploads/2026/03/Aurora7-1024x402.png)
- content / image: [Durability, High Availability and Disaster Recovery with Amazon Aurora](https://www.percona.com/wp-content/uploads/2026/03/Aurora8.png)

## Auteur source

Brian has over 20 years of experience in the database and technology space. He holds a Bachelor's degree in Organizational Leadership. Brian's career has included roles as Database Architect, Solutions Architect, Product Manager, and Pre-Sales Engineer. Brian spent 7 years at Teradata on their product engineering and product management teams followed by 7 years with Oracle as a Principal Sales Consultant. Brian is currently working at Percona as the Director of Solution Engineering.
