# User Data Management System

## Overview

The User Data Management System is a comprehensive backend solution that allows users to save, track, and manage their business data including financial projections, business plans, market analysis, and other important information throughout the InnoStart platform.

## Features

### 🗄️ Data Storage & Management
- **Save Financial Projections**: Store complete financial projection data with all parameters and results
- **Business Plan Storage**: Save business plan content and analysis
- **Market Analysis**: Store market research and competitor analysis
- **Custom Data Types**: Support for any custom business data
- **Data Versioning**: Track changes and maintain history of all modifications

### 🔍 Search & Filter
- **Full-Text Search**: Search across titles, descriptions, and content
- **Type Filtering**: Filter by data type (financial, business plan, etc.)
- **Status Filtering**: Filter by status (draft, completed, archived)
- **Tag-Based Organization**: Organize data with custom tags
- **Date Range Filtering**: Filter by creation or modification dates

### 📊 Data Analytics
- **Usage Statistics**: Track total items, completed projects, drafts, and favorites
- **Activity History**: Complete audit trail of all data operations
- **Performance Metrics**: Monitor data usage patterns and trends

### 🔄 Data Operations
- **Create**: Save new data with metadata
- **Read**: View and search existing data
- **Update**: Modify existing data with change tracking
- **Delete**: Remove data with confirmation
- **Archive**: Archive old data without deletion
- **Restore**: Restore archived data

### 📤 Export & Import
- **Multiple Formats**: Export to PDF, Excel, CSV, JSON, Word
- **Batch Operations**: Export multiple items at once
- **Template System**: Create and use reusable templates
- **Data Import**: Import data from external sources

## Database Schema

### Core Tables

#### `user_saved_data`
Main table for storing user data with comprehensive metadata.

```sql
CREATE TABLE user_saved_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    data_type ENUM('financial_projection', 'business_plan', 'market_analysis', 'competitor_analysis', 'marketing_strategy', 'custom') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    data_content JSON NOT NULL,
    tags JSON,
    is_favorite BOOLEAN DEFAULT FALSE,
    is_public BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'completed', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `user_data_history`
Tracks all changes and operations on user data.

```sql
CREATE TABLE user_data_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    data_id INT NOT NULL,
    data_type ENUM(...) NOT NULL,
    action ENUM('created', 'updated', 'deleted', 'archived', 'restored') NOT NULL,
    old_data JSON,
    new_data JSON,
    change_summary TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `user_data_templates`
Stores reusable templates for different data types.

```sql
CREATE TABLE user_data_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    template_type ENUM(...) NOT NULL,
    template_data JSON NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `user_data_exports`
Tracks export operations and file management.

```sql
CREATE TABLE user_data_exports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    data_id INT NOT NULL,
    data_type ENUM(...) NOT NULL,
    export_format ENUM('pdf', 'excel', 'csv', 'json', 'word') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    file_size INT,
    export_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## API Endpoints

### User Data API (`/api/user-data.php`)

#### POST - Save Data
```javascript
{
    "action": "save_data",
    "data_type": "financial_projection",
    "title": "My Business Projections",
    "description": "Financial projections for my restaurant",
    "data_content": { /* your data */ },
    "tags": ["financial", "restaurant"],
    "status": "completed"
}
```

#### GET - Retrieve Data
```
GET /api/user-data.php?action=get_user_data&data_type=financial_projection&limit=50
```

#### PUT - Update Data
```javascript
{
    "action": "update_data",
    "data_id": 123,
    "title": "Updated Title",
    "description": "Updated description",
    "status": "completed"
}
```

#### DELETE - Delete Data
```javascript
{
    "action": "delete_data",
    "data_id": 123
}
```

#### GET - Search Data
```
GET /api/user-data.php?action=search_data&query=restaurant&data_type=financial_projection
```

#### GET - Data History
```
GET /api/user-data.php?action=get_data_history&data_id=123
```

## Frontend Integration

### Financial Projections Integration

The system is fully integrated with the financial projections feature:

1. **Save Button**: Added to financial projections form
2. **Load Button**: Load previously saved projections
3. **Auto-Save**: Optional automatic saving of draft data
4. **Template System**: Use pre-built templates for different business types

### My Data Dashboard

A comprehensive dashboard section (`#my-data`) provides:

1. **Data Overview**: Statistics and summary cards
2. **Data List**: Searchable and filterable list of all user data
3. **Activity History**: Recent changes and operations
4. **Quick Actions**: View, edit, delete, and export operations

### JavaScript API

#### User Data Manager
```javascript
// Save financial projection
const dataId = await userDataManager.saveFinancialProjection(projectionData, title, description);

// Load user data
const data = await userDataManager.loadUserData('financial_projection', 50);

// Search data
const results = await userDataManager.searchData('restaurant');

// Delete data
const success = await userDataManager.deleteData(dataId);
```

#### My Data Manager
```javascript
// Initialize My Data section
await myDataManager.init();

// View specific data
await myDataManager.viewData(dataId);

// Edit data
await myDataManager.editData(dataId);
```

## Setup Instructions

### 1. Database Setup
Run the setup script to create all necessary tables:

```bash
php setup_user_data_system.php
```

### 2. File Permissions
Ensure the following directories are writable:
- `uploads/` - For exported files
- `logs/` - For system logs
- `temp/` - For temporary files

### 3. API Configuration
The API automatically handles:
- User authentication via session
- Data validation and sanitization
- Error handling and logging
- CORS headers for cross-origin requests

## Usage Examples

### Saving Financial Projections

1. **Generate Projections**: Use the financial projections tool
2. **Click Save**: Click the "Save Projection" button
3. **Add Metadata**: Enter title and description
4. **Confirm**: Data is saved with full history tracking

### Loading Saved Data

1. **Access My Data**: Navigate to "My Data" section
2. **Search/Filter**: Use search and filter options
3. **Select Data**: Click on desired data item
4. **Load**: Data is loaded into the appropriate tool

### Managing Data

1. **View**: See complete data with metadata
2. **Edit**: Modify titles, descriptions, and status
3. **Archive**: Move old data to archive
4. **Delete**: Remove data permanently (with confirmation)
5. **Export**: Download data in various formats

## Security Features

### Data Privacy
- **User Isolation**: All data is isolated by user ID
- **Session Authentication**: Secure session-based authentication
- **Data Validation**: Comprehensive input validation and sanitization
- **SQL Injection Protection**: Prepared statements for all database operations

### Access Control
- **User-Specific Data**: Users can only access their own data
- **Public/Private Toggle**: Optional public sharing of templates
- **Role-Based Access**: Foundation for future role-based permissions

### Audit Trail
- **Complete History**: Every operation is logged with timestamps
- **Change Tracking**: Before/after data snapshots for updates
- **User Attribution**: All actions are attributed to specific users

## Performance Optimizations

### Database Indexing
- **User ID Index**: Fast user-specific queries
- **Data Type Index**: Efficient filtering by type
- **Status Index**: Quick status-based filtering
- **Date Indexes**: Optimized date range queries

### Caching Strategy
- **Session Caching**: User data cached in session
- **Template Caching**: Frequently used templates cached
- **Query Optimization**: Optimized database queries

### Pagination
- **Large Dataset Support**: Pagination for large data sets
- **Lazy Loading**: Load data on demand
- **Infinite Scroll**: Optional infinite scroll for better UX

## Future Enhancements

### Planned Features
1. **Data Sharing**: Share data between users
2. **Collaboration**: Multi-user editing capabilities
3. **Advanced Analytics**: Detailed usage analytics
4. **API Rate Limiting**: Protect against abuse
5. **Data Backup**: Automated backup system
6. **Mobile App**: Native mobile application
7. **Real-time Sync**: Real-time data synchronization
8. **Advanced Search**: Elasticsearch integration

### Integration Opportunities
1. **Business Plan Generator**: Integrate with business plan creation
2. **Market Analysis Tools**: Connect with market research tools
3. **Financial Calculators**: Link with various financial calculators
4. **Reporting System**: Generate comprehensive reports
5. **Notification System**: Email/SMS notifications for important changes

## Troubleshooting

### Common Issues

#### Data Not Saving
- Check user authentication
- Verify database connection
- Check file permissions
- Review error logs

#### Search Not Working
- Verify search query format
- Check database indexes
- Review API response
- Test with simple queries

#### Performance Issues
- Check database query performance
- Review indexing strategy
- Monitor memory usage
- Optimize pagination

### Error Codes
- `400`: Bad Request - Invalid input data
- `401`: Unauthorized - User not authenticated
- `404`: Not Found - Data not found
- `500`: Internal Server Error - Server-side error

## Support

For technical support or feature requests:
1. Check the error logs in `logs/` directory
2. Review the API responses in browser developer tools
3. Verify database connectivity and permissions
4. Test with the provided setup script

## Conclusion

The User Data Management System provides a robust, scalable solution for managing business data within the InnoStart platform. It offers comprehensive features for data storage, retrieval, search, and management while maintaining security and performance standards.

The system is designed to grow with user needs and can be extended to support additional data types and features as the platform evolves.
