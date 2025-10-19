# Financial Projections Feature

## Overview

The Financial Projections feature is a comprehensive tool that allows entrepreneurs to create detailed financial forecasts for their business ventures. This feature provides interactive calculations, visual charts, and export capabilities to help users make informed financial decisions.

## Features

### 🧮 Financial Calculator
- **Business Information Input**: Name, type, and projection period
- **Financial Parameters**: Revenue, expenses, growth rates, tax rates
- **Advanced Settings**: Inflation, discount rates, break-even analysis
- **Real-time Calculations**: Instant updates as parameters change

### 📊 Visual Analytics
- **Revenue vs Expenses Chart**: Line chart showing monthly performance
- **Profit Distribution**: Doughnut chart displaying profit vs expenses
- **Interactive Tables**: Detailed monthly projections with color-coded values
- **Summary Cards**: Key financial metrics at a glance

### 💾 Data Management
- **Save Projections**: Store calculations for future reference
- **Load Previous**: Access saved projections
- **Export Options**: PDF, Excel, CSV, JSON formats
- **Business Templates**: Pre-configured settings for different business types

### 🎯 Business Intelligence
- **ROI Calculations**: Return on investment analysis
- **Payback Period**: Time to recover initial investment
- **Break-even Analysis**: When the business becomes profitable
- **Growth Projections**: Multi-year financial forecasting

## File Structure

```
├── dashboard.html                    # Main dashboard with financial projections section
├── assets/
│   ├── css/
│   │   └── dashboard.css            # Styling for financial projections
│   └── js/
│       └── dashboard.js             # JavaScript functionality
├── api/
│   └── financial-projections.php    # Backend API for calculations and data storage
├── database/
│   └── innostart_database.sql       # Database schema with financial_projections table
├── setup_financial_projections.php  # Setup script for the feature
└── FINANCIAL_PROJECTIONS_FEATURE.md # This documentation
```

## Installation

### 1. Database Setup
Run the setup script to create the necessary database table:

```bash
php setup_financial_projections.php
```

### 2. Database Schema
The feature requires a `financial_projections` table:

```sql
CREATE TABLE IF NOT EXISTS financial_projections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    projection_data JSON NOT NULL,
    status ENUM('draft', 'completed', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3. File Permissions
Ensure the following directories are writable:
- `uploads/` (for exported files)
- `logs/` (for error logging)

## Usage

### Accessing the Feature
1. Login to your InnoStart dashboard
2. Click on "Financial Projections" in the sidebar
3. Fill in the business information form
4. Click "Generate Financial Projections"

### Input Parameters

#### Business Information
- **Business Name**: Your company name
- **Business Type**: Select from predefined types (Restaurant, Retail, Service, etc.)
- **Projection Period**: 1, 2, 3, or 5 years

#### Financial Inputs
- **Initial Investment**: Startup capital required
- **Monthly Revenue**: Expected monthly income
- **Monthly Expenses**: Expected monthly costs
- **Annual Growth Rate**: Expected revenue growth percentage

#### Advanced Parameters
- **Tax Rate**: Business tax percentage (default: 15%)
- **Inflation Rate**: Expected inflation (default: 5%)
- **Discount Rate**: Risk-adjusted discount rate (default: 12%)
- **Break-even Period**: Expected months to profitability

### Business Type Templates

The system includes predefined templates for common business types:

| Business Type | Default Revenue | Default Expenses | Growth Rate |
|---------------|----------------|------------------|-------------|
| Restaurant | 5M RWF | 3.5M RWF | 15% |
| Retail | 3M RWF | 2M RWF | 20% |
| Service | 2M RWF | 1.2M RWF | 25% |
| Tourism | 8M RWF | 5M RWF | 12% |
| Agriculture | 4M RWF | 2.5M RWF | 10% |
| Technology | 6M RWF | 3M RWF | 30% |

## API Endpoints

### POST /api/financial-projections.php

#### Calculate Projection
```json
{
    "action": "calculate_projection",
    "parameters": {
        "businessName": "My Restaurant",
        "businessType": "restaurant",
        "projectionPeriod": 3,
        "initialInvestment": 10000000,
        "monthlyRevenue": 5000000,
        "monthlyExpenses": 3500000,
        "growthRate": 15,
        "taxRate": 15,
        "inflationRate": 5,
        "discountRate": 12,
        "breakEvenMonths": 12
    }
}
```

#### Save Projection
```json
{
    "action": "save_projection",
    "projection": {
        "businessName": "My Restaurant",
        "businessType": "restaurant",
        "summary": {...},
        "projections": [...],
        "parameters": {...}
    }
}
```

#### Get User Projections
```json
{
    "action": "get_projections"
}
```

#### Delete Projection
```json
{
    "action": "delete_projection",
    "projectionId": 123
}
```

### GET /api/financial-projections.php

#### Get Business Templates
```
?action=get_templates
```

#### Get Financial Metrics
```
?action=get_metrics
```

## Calculations

### Revenue Calculation
```
Monthly Revenue = Base Revenue × Growth Factor × Inflation Factor
Growth Factor = (1 + Growth Rate/100)^(Month/12)
Inflation Factor = (1 + Inflation Rate/100)^(Month/12)
```

### Expense Calculation
```
Monthly Expenses = Base Expenses × Inflation Factor
```

### Profit Calculation
```
Gross Profit = Revenue - Expenses
Tax = max(0, Gross Profit × Tax Rate/100)
Net Profit = Gross Profit - Tax
Cumulative Profit = Previous Cumulative + Net Profit
```

### Key Metrics
- **Total Revenue**: Sum of all monthly revenues
- **Total Expenses**: Sum of all monthly expenses
- **Net Profit**: Total Revenue - Total Expenses - Total Tax
- **Profit Margin**: (Net Profit / Total Revenue) × 100
- **ROI**: (Final Cumulative Profit / Initial Investment) × 100
- **Payback Period**: Month when cumulative profit becomes positive

## Export Formats

### PDF Export
- Professional formatted report
- Includes summary, charts, and detailed table
- Print-ready layout

### Excel Export
- CSV format compatible with Excel
- All monthly data included
- Formulas for further analysis

### CSV Export
- Raw data format
- Suitable for data analysis tools
- Machine-readable format

### JSON Export
- Complete projection data
- Includes all parameters and calculations
- Suitable for API integration

## Styling and Theming

### CSS Classes
- `.financial-input-section`: Input form styling
- `.financial-chart-container`: Chart container styling
- `.financial-metric`: Summary card styling
- `.metric-positive`: Green color for positive values
- `.metric-negative`: Red color for negative values

### Dark Mode Support
The feature includes comprehensive dark mode support with appropriate color schemes and contrast ratios.

### Responsive Design
- Mobile-first approach
- Adaptive layouts for different screen sizes
- Touch-friendly interface elements

## Error Handling

### Client-side Validation
- Required field validation
- Numeric input validation
- Range validation for percentages
- Business logic validation

### Server-side Validation
- Input sanitization
- SQL injection prevention
- Authentication checks
- Error logging

### Error Messages
- User-friendly error messages
- Detailed logging for debugging
- Graceful degradation

## Performance Considerations

### Frontend Optimization
- Lazy loading of charts
- Debounced input validation
- Efficient DOM updates
- Chart.js optimization

### Backend Optimization
- Database indexing
- Query optimization
- Caching strategies
- API rate limiting

## Security Features

### Authentication
- User session validation
- API endpoint protection
- Role-based access control

### Data Protection
- Input sanitization
- SQL injection prevention
- XSS protection
- CSRF tokens

### Privacy
- User data isolation
- Secure data storage
- Audit logging

## Browser Compatibility

### Supported Browsers
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

### Required Features
- ES6 support
- Canvas API (for charts)
- Local Storage
- Fetch API

## Troubleshooting

### Common Issues

#### Charts Not Displaying
- Check Chart.js library loading
- Verify canvas element exists
- Check browser console for errors

#### Calculations Incorrect
- Verify input validation
- Check parameter ranges
- Review calculation formulas

#### Export Not Working
- Check file permissions
- Verify browser download settings
- Check server error logs

#### Database Errors
- Verify table exists
- Check user permissions
- Review connection settings

### Debug Mode
Enable debug mode by adding `?debug=1` to the URL to see detailed error information.

## Future Enhancements

### Planned Features
- Scenario analysis (best/worst case)
- Monte Carlo simulations
- Industry benchmarking
- Integration with accounting software
- Multi-currency support
- Advanced reporting templates

### API Improvements
- GraphQL support
- Real-time updates
- Webhook notifications
- Bulk operations

## Support

For technical support or feature requests:
- Check the documentation
- Review error logs
- Contact the development team
- Submit issues via the project repository

## License

This feature is part of the InnoStart platform and follows the same licensing terms.
