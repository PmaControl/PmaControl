---
title: 'Percona Operator for MongoDB 1.22.0: Automatic Storage Resizing, Vault Integration, Service Mesh Support, and More!'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-operator-for-mongodb-1-22-0-automatic-storage-resizing-vault-integration-service-mesh-support-and-more/
  post_id: 35671
source_author:
  name: Slava Sarzhan
  slug: slava-sarzhan
  url: https://www.percona.com/blog/author/slava-sarzhan/
  website: ''
published_at: '2026-02-25T17:09:22'
published_at_gmt: '2026-02-25T17:09:22'
modified_at: '2026-03-26T20:13:08'
modified_at_gmt: '2026-03-26T20:13:08'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Cloud
- MongoDB
- Open Source
- Percona Software
category_slugs:
- cloud
- mongodb
- open-source
- percona-software
tags:
- backup
- Kubernetes Operator
- mongodb operators
- new release
- PSMDB
- Release
tag_slugs:
- backup
- kubernetes-operator
- mongodb-operators
- new-release
- psmdb
- release
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/From-Feature-Request-to-Release-How-Community-Feedback-Shaped-PBMs-Alibaba-Cloud-Integration.jpg
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Operator for MongoDB 1.22.0: Automatic Storage Resizing, Vault Integration, Service Mesh Support, and More!

Source: [Percona Blog](https://www.percona.com/blog/percona-operator-for-mongodb-1-22-0-automatic-storage-resizing-vault-integration-service-mesh-support-and-more/)

Auteur source: [Slava Sarzhan](https://www.percona.com/blog/author/slava-sarzhan/)

Publication: 2026-02-25T17:09:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The latest release of the Percona Operator for MongoDB, 1.22.0 is here. It brings automatic storage resizing, HashiCorp Vault integration for system user credentials, better integration with service meshes, improved backup and restore options, and more. This post walks through the highlights and how they can help your MongoDB deployments on Kubernetes. Percona Operator for MongoDB … Continued

## Structure detectee

- H2: Percona Operator for MongoDB 1.22.0
- H2: Automatic Storage Resizing
- H2: Vault Integration for System User Password Management
- H2: Service Mesh Integration
- H2: Logcollector Improvement: Configure Log Rotation for Persistent Logs
- H2: Backup and Restore
- H3: Restore to a Cluster with Different Replica Set Names
- H3: Configurable Timeout for PBM to Start Backups
- H3: Support of the MinIO Storage Type for S3-Compatible storage service
- H3: Private CA for TLS with S3-Compatible Storage
- H3: Cluster Readiness Now Reflects PBM State
- H2: Additional Customization Features
- H3: Hook Script Support
- H3: Ability to Define Custom Environment Variables
- H2: Deprecation: PMM2 Support
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Operator for MongoDB 1.22.0: Automatic Storage Resizing, Vault Integration, Service Mesh Support, and More!](https://www.percona.com/wp-content/uploads/2026/03/From-Feature-Request-to-Release-How-Community-Feedback-Shaped-PBMs-Alibaba-Cloud-Integration.jpg)
- content / image: [pvc_resize-1024x572.png](https://www.percona.com/wp-content/uploads/2026/03/pvc_resize-1024x572.png)
- content / image: [vault_int-1024x572.png](https://www.percona.com/wp-content/uploads/2026/03/vault_int-1024x572.png)
- content / image: [logrotation-1024x572.png](https://www.percona.com/wp-content/uploads/2026/03/logrotation-1024x572.png)
- content / image: [remappping-1024x572.png](https://www.percona.com/wp-content/uploads/2026/03/remappping-1024x572.png)
- content / image: [custom_f-1024x572.png](https://www.percona.com/wp-content/uploads/2026/03/custom_f-1024x572.png)

## Auteur source

Head of Cloud Native Engineering from Lviv, Ukraine, with a passion for building smarter, more efficient Kubernetes solutions. I joined Percona in January 2019, starting as a build/release engineer managing Jenkins farms and automating testing and release processes. Soon after, I moved to the cloud team, creating testing infrastructure and developing new features for Kubernetes operators. Over the years, I’ve grown into leadership, guiding distributed engineering teams to deliver scalable, production-ready solutions while fostering a culture of ownership, clarity, and continuous improvement. Kubernetes isn’t just a tool for me, it’s a playground for innovation. I focus on solving complex problems, improving systems, and sharing insights with the community.
