---
title: 'Using AWS EC2 instance store vs EBS for MySQL: how to increase performance and decrease cost'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-aws-ec2-instance-store-vs-ebs-for-mysql-how-to-increase-performance-and-decrease-cost/
  post_id: 19124
source_author:
  name: Alexander Rubin
  slug: alexanderrubin
  url: https://www.percona.com/blog/author/alexanderrubin/
  website: http://www.percona.com/blog
published_at: '2018-08-20T12:07:14'
published_at_gmt: '2018-08-20T12:07:14'
modified_at: '2026-05-05T19:19:44'
modified_at_gmt: '2026-05-05T19:19:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Cloud
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- ebs
- ec2
- ec2 instance store
tag_slugs:
- ebs
- ec2
- ec2-instance-store
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/AWS-EC2-MySQL-cost-savings-1.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using AWS EC2 instance store vs EBS for MySQL: how to increase performance and decrease cost

Source: [Percona Blog](https://www.percona.com/blog/using-aws-ec2-instance-store-vs-ebs-for-mysql-how-to-increase-performance-and-decrease-cost/)

Auteur source: [Alexander Rubin](https://www.percona.com/blog/author/alexanderrubin/)

Publication: 2018-08-20T12:07:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you are using large EBS GP2 volumes for MySQL (i.e. 10TB+) on AWS EC2, you can increase performance and save a significant amount of money by moving to local SSD (NVMe) instance storage. Interested? Then read on for a more detailed examination of how to achieve cost-benefits and increase performance from this implementation. EBS … Continued

## Structure detectee

- H2: EBS vs Local instance store
- H3: A look at costs
- H2: How to migrate to local storage from EBS
- H3: Compression
- H3: ZFS
- H3: MyRocks
- H2: Replication and using local volumes
- H2: Other options
- H2: Conclusions
- H3: You May Also Like

## Images et graphiques reperes

- featured / image: [Using AWS EC2 instance store vs EBS for MySQL: how to increase performance and decrease cost](https://www.percona.com/wp-content/uploads/2026/03/AWS-EC2-MySQL-cost-savings-1.jpg)
- content / image: [AWS EC2 MySQL cost savings](https://www.percona.com/wp-content/uploads/2026/03/AWS-EC2-MySQL-cost-savings-1-300x199.jpg)
- content / image: [MySQL Master AZ 1a, Local storage](https://www.percona.com/wp-content/uploads/2026/03/master-slave.png)

## Auteur source

Alexander joined Percona in 2013. Alexander worked with MySQL since 2000 as DBA and Application Developer. Before joining Percona he was doing MySQL consulting as a principal consultant for over 7 years (started with MySQL AB in 2006, then Sun Microsystems and then Oracle). He has helped many customers design large, scalable and highly available MySQL systems and optimize MySQL performance. Alexander has also helped customers design Big Data stores with Apache Hadoop and related technologies.
