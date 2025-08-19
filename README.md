==Medveda============================================================
# MedVeda
======================================================================

## 1. Team Information
* *Team Name:* Team TechPandit
* *Team Members:*
    * Kunal Kathare
    * Rajashekhar Durgad
    * Shrikant Chavan
    * Krishna Jadhav
    * VaibhavKumar Mujumdar

---

## 2. Problem Addressed
MedChain: Healthcare Supply Integrity Platform
---


## 3. Our Solution & Key Features
MedVeda is a web-based platform that brings transparency, security, and integrity to the pharmaceutical supply chain. By generating a unique digital identity and hologram for each product, our system provides an immutable audit trail, allowing every stakeholder to track and verify medicines at every step.

This ensures that only genuine products reach the patients.

* *Key Features:*

#### For Manufacturers (Admin)
* *Product Registration & Hologram Generation:* Add new products to the system, which automatically generates a unique ID and a secure hologram for authentication.
* *Complete Audit Trail:* View a detailed history of a product's journey, including every location, handler, and transaction from start to finish.
* *Live Product Tracking:* Monitor the real-time delivery status and location of product shipments.

#### For Distributors
* *Order Management:* Efficiently manage the pickup of orders from the manufacturer.
* *Transaction Recording:* Securely record the sale and transfer of medicines to pharmacists and hospitals.

#### For Pharmacists & Hospitals
* *Authenticity Verification:* Instantly verify a product's legitimacy by scanning its hologram or entering its unique identification ID.
* *Secure Dispensing:* Dispense medicines to patients with the confidence that they are genuine and have been tracked securely.

Medveda Patients
* *View Authenticity Certificate:* Scan a product to view its digital certificate of authenticity, providing peace of mind.
* *Check Local Stock:* Check the real-time availability of specific medicines in nearby registered pharmacies.

---

## 4. Technologies Used
* *Frontend:* [e.g., HTML, CSS, JavaScript, Bootstrap]
* *Backend:* [e.g., PHP]
* *Database:* [e.g., MySQL]
* *Server Environment:* XAMPP
* *Other Tools:* [e.g., Git, GitHub, VS Code]

---

## 5. How to Run and Test the Project
Follow these steps to set up and run the project on your local machine using XAMPP.

### Prerequisites
* You must have *XAMPP* installed on your computer. You can download it from [https://www.apachefriends.org](https://www.apachefriends.org).

### Step-by-Step Installation
1.  *Clone the Repository:*
    Open your terminal or command prompt and run:
    
    git clone https://github.com/techpkunal/Medveda.git
    
    Alternatively, you can download the project as a ZIP file and extract it.

2.  **Move Project to htdocs:**Medveda
    * Copy tMedvedae project folder.
    * Navigate to your XAMPP installation directory (e.g., C:\xampp on Windows).
    * Paste the project folder inside the htdocs directory.

3.  *Start XAMPP Servers:*
    * Open the *XAMPP Control Panel*.
    * Click the *Start* button for both the *Apache* and *MySQL* modules.

4.  *Import the Database (If applicable):*
    * Open your web browser and go to http://localhost/phpmyadmin.
    * Click on the *"New"* button on the left sidebar to create a new database. Name it [your_database_name].
    * Select the newly created database.
    * Click on the *"Import"* tab at the top.
    * Click *"Choose File"* and select the .sql file located in your project's database folder (e.g., database/database.sql).
    * Click the *"Go"* button at the bottom to start the import.
Medveda
5.  *Access the Project:*
    * Open your web browser.
    * Navigate to: http://localhost/[your-project-folder-name]
    * (Replace [your-project-folder-name] with the actual name of the folder you placed in htdocs).

The project should now be running in your browser!

---

## 6. License
This project is licensed under the MIT License. See the LICENSE file for more details.
MedvedaMedveda
