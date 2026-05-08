---
title: Querying Archived RDS Data Directly From an S3 Bucket
source:
  name: Percona Blog
  url: https://www.percona.com/blog/querying-archived-rds-data-directly-from-an-s3-bucket/
  post_id: 25130
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2021-11-24T13:45:53'
published_at_gmt: '2021-11-24T13:45:53'
modified_at: '2026-05-05T17:36:21'
modified_at_gmt: '2026-05-05T17:36:21'
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
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- athena
- Aurora
- cloud
- MySQL
- mysql-and-variants
- RDS
- S3
tag_slugs:
- athena
- aurora
- cloud
- mysql
- mysql-and-variants
- rds
- s3
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/querying-archived-rds-data-from-s3-bucket.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Querying Archived RDS Data Directly From an S3 Bucket

Source: [Percona Blog](https://www.percona.com/blog/querying-archived-rds-data-directly-from-an-s3-bucket/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2021-11-24T13:45:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A recommendation we often give to our customers is along the lines of “archive old data” to reduce your database size. There is a tradeoff between keeping all our data online and archiving part of it to cold storage. There could also be legal requirements to keep certain data online, or you might want to … Continued

## Structure detectee

- H2: Archiving Data to S3
- H2: Querying the Archived Data
- H2: Removing the Archived Data from the Database

## Images et graphiques reperes

- featured / image: [Querying Archived RDS Data Directly From an S3 Bucket](https://www.percona.com/wp-content/uploads/2026/03/querying-archived-rds-data-from-s3-bucket.png)
- content / image: [querying archived rds data from s3 bucket.png](https://www.percona.com/wp-content/uploads/2026/03/querying-archived-rds-data-from-s3-bucket-300x168.png)
- content / image: [Archiving Data to S3](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-11-19-at-3.36.05-PM-1-1024x854.png)
- content / image: [Export the snapshot to Amazon S3](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-11-19-at-3.36.51-PM-1024x1004.png)
- content / image: [IAM role](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-11-19-at-3.37.00-PM-1024x413.png)
- content / image: [Amazon Athena](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2021-11-19-at-3.37.06-PM-1024x466.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
