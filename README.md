# JOB-SEARCH-PORTAL-PROJECT
 Implemented a Job Portal Web Application based on the Client-Server Model, enabling interaction between job seekers and recruiters.  Frontend: HTML5, CSS3, JavaScript, PHP Backend: PHP, MySQL Database: MySQL This project demonstrates seamless data communication, user authentication, and dynamic content management using web technologies.
INTRODUCTION---
Overview:-- Job Portal Pro is a dynamic web application designed to streamline the job
search and hiring process. It enables job seekers to find and apply for jobs, and
allows employers to post and manage openings with ease.
Purpose:--•Bridge the gap between candidates and employers
                     •Automate and simplify the recruitment workflow
                     •Deliver a user friendly and scalable hiring
                     •platform
Scope:--•Job seeker and employer modules
                •Admin panel for moderation
                •Real time notifications and smart filters
SYSTEM ARCHITECTURE---
Design Approach:--Client Server Model using Web Technologies
Key Components:--•Login Page: Role based access (Seeker/Employer)
                                       •Home/Dashboard: Personalized based on user role
                                       •Job Search & Filters: Real time, skill/location based
                                       •Application Form: Resume upload, validation
                                       •Database (MySQL): Stores users, jobs, applications
Technologies Used:--•Frontend: HTML5, CSS3, JavaScript ,PHP
                                         •Backend: PHP ,MySQL
                                         •Database: MySQL
KEY FEATURES---
1.Job Search Functionality:•JAX based live search
                                                         •Filtering by role, location, experience, salary
2.Application System:--•Pre filled forms with resume upload
                                                •Secure database submission
3.Authentication & Authorization:--•Role based login (job seeker/employer)
                                                                          •Secure sessions, hashed passwords
4.Admin Panel (Optional):--•Monitor users, control job categories
TECHNICAL DETAILS--
Database Schema:•Tables : users, employers, job_seekers, jobs, applications
                                    •Enforces foreign key relationships and normalization
Backend (PHP + MySQL):-•Role based routing (employer/job seeker).
                                                     •Secure file uploads (e.g., resumes).
                                                     •CRUD for jobs & applications.
                                                     •Session based authentication.
Frontend :--•Login Page : Role selector,
                        •Dashboard : Employer vs. seeker
                        •Job Details Page : Job info + apply
DATABASE DESIGN---The portal uses MySQL as the backend database, storing all data related to job listings and applications.
1. recruiter_jobs Table:--•Stores job related information created by recruiters.
                                                  •Fields include job_id, title, description, location, qualifications, and posted date.
                                                  •Acts as the source for listing available jobs.
2. job_application Table:--•Stores user submitted application data.
                                                    •Fields include application_id, user_id, job_id, resume path, and application status.
                                                    •Enables tracking of who applied for which job and facilitates screening.
                                                     •Both tables are connected via foreign key (job_id) to relate applications with specific job posts,ensuring relational integrity.
CONCLUSION &FUTURE ENCHANCEMENT---
Project Summary:-- Job Portal Pro meets its goal of providing a reliable, efficient platform for digital
recruitment. It simplifies hiring, ensures role based access, and securely manages
user data.
