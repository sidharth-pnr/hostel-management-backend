# Hostel Room Allocation and Complaint Management System - Mini Project Context
- Website Application Name is "Campus Housing"
## Project Architecture
- **Backend:** API located in `C:\xampp\htdocs\hostel_room_api` (PHP/XAMPP environment)
- **Frontend:** React application located in `C:\Users\LENOVO\OneDrive\Desktop\Group 15\hostel-management`
- **Data Priority:** Core tables (`students`, `rooms`, `complaints`, `room_assignments`, `admins`) are essential. The `activity_log` table is non-essential and should not be used for critical data dependencies.

## Operational Rules
- Always consider the impact on both frontend and backend when making changes.
- Adhere to existing naming conventions in both repositories.
- For backend changes, ensure explicit confirmation before modification.

## Why this project
- Role: Act as an expert Software Engineer and Project Mentor.
- ​Context: I am developing a Hostel Room Allocation System for my KTU Computer Science Mini Project (CSD334). You must help me follow the official 2019 scheme syllabus requirements.  
- ​Instructions:
Please help me execute the project by following these specific phases outlined in my teaching plan:
​Requirement Analysis: Help me create a formal Software Requirements Specification (SRS).  
- ​System Design: Generate a Software Design Document (SDD) including:
​System and Application Architecture.  
​Detailed Database Design and API Design.  
​GUI Mockups and User Experience flow.  
- ​Development Setup: Guide me in setting coding standards and configuring Git for Source Code Control.  
​- Implementation: Provide modular code following the design, ensuring it is scalable and secure.  
​- Testing: Help me write a Test Plan with Test Scenarios, Functional Testing, and a Traceability Matrix.  
​- Documentation: Finally, help me structure a 40-page technical report in Times New Roman, ensuring proper Figure/Table numbering and a suggestive order of documentation (Abstract, Certification, Chapters, etc.).  
- ​Current Goal: Let's start with Step 1. Based on my project title, please generate a detailed Problem Statement and the initial Functional Requirements for my SRS.
​Key Requirements to Remember
​To ensure you get the full 150 marks, keep these syllabus constraints in mind as you work with the AI:  
​- Group Size: Ensure you are working in a group of 3 or 4 members.  
​- Mark Distribution: Your Internal (CIE) is worth 75 marks (based on attendance, guide feedback, and your report), and your External (ESE) is 75 marks (Presentation, Demo, and Viva).  
- ​Report Format: The final report must be at least 40 pages. Use 1.5 line spacing for body text and ensure all figures have titles under the image.  
- ​Technical Depth: The evaluation committee will look for innovative design, scalability, and security.

- Role: Expert Full-Stack Web Developer & KTU Academic Mentor.
- ​Context: I am developing a "Hostel Room Allocation and Complaint Management System" as a web application for my KTU Computer Science Mini Project (Course Code: CSD334). The project must strictly adhere to the Software Engineering principles and Teaching Plan outlined in the 2019 Scheme syllabus.  
- ​Project Requirements & Technical Stack:
- ​System Goal: A web-based portal for Students (Registration, Room Viewing, Complaint Tracking) and Admins (Approval, Allocation, Complaint Management, Fee Management).
- ​Core Modules: Student Management, Hostel & Room Management, Room Allocation, and Complaint Management.
​- Standards: Must include a Digital ID Card, date tracking for all actions, and a "Kerala Style" UI design.
​- Documentation: All code must eventually support a 40-page technical report including SRS, SDD (Architecture, Database Schema, API Design), and a Test Plan.  
​- Instructions for Gemini CLI:
​- Analyze Gemini.md: Read all project-specific notes and feature lists I have provided in this file.
​- Generate SRS & SDD: First, output a formal Software Requirements Specification and a Database Schema (SQL/NoSQL) that maps to the identified modules.  
- ​Code Scaffolding: Generate a modular web project structure.
​Set up Source Code Control (Git) conventions.  
​Provide the boilerplate for the Student and Admin dashboards.
​Ensure the Complaint Management features are integrated into the relevant modules rather than being a standalone isolated block.
- ​Unit & Integration Testing: For every feature generated, provide a corresponding test scenario for the Test Case Document.  
- ​Adherence: Do not add external algorithms; use only the logic required for hostel management. Ensure the App.jsx (if using React) maintains connections between all components for a "real" application, not just a demo.
- ​Current Task: Start by generating the Database Schema and the System Architecture Design (Chapter 4 of the SDD) for the Hostel Management System.

- Role: KTU Academic Auditor and Senior Web Developer.
- ​Context: I have a functional website for my "Hostel Room Allocation and Complaint Management System." I need to refactor it to strictly follow the KTU 2019 Scheme Mini Project (CSD334) requirements.  
​Instructions:
- ​Syllabus Audit: Analyze my current Gemini.md and codebase against the Teaching Plan.  
- ​Feature Pruning: Identify and suggest the removal of any "unwanted things" or "extra designs" that do not contribute to the core
- objectives: Functional Specifications, Performance, Scalability, and Security.  
​- Module Integration: Ensure the Complaint Management features are not isolated but integrated into the Student/Admin workflows as per my previous instructions.
​- Standardization: Ensure the following specific KTU requirements are met:
​- Source Code Control: Prepare the project for Git/Subversion setup.  
​- Documentation Alignment: Every feature must be map-able to the SRS (Step 3) and SDD (Step 4).  
​- Testing Hooks: Ensure the code supports Unit Testing and Integration Testing reports.  
​- Refactor Request: Refactor the App.jsx and main components to maintain real application connectivity, removing any "presentation-only" hardcoded data.
​- Current Task: Review my current file structure and list which components/features are "unwanted" according to the KTU syllabus.  
​Key Things to "Remove" Based on the PDF
​To keep your project clean for the examiners, the syllabus suggests focusing on Software Engineering principles rather than flashy, irrelevant features. Ensure you don't have:  
​- Excessive external libraries: The syllabus emphasizes "sound knowledge in any programming language".  
​- Non-functional "Presentation" pages: The project must be demonstrated for its full design specifications.  
​Disconnected UI: KTU values Aesthetics/Ergonomics and User Experience, but only if they serve the functional objectives.