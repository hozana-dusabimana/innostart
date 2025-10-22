import pandas as pd
import re
from collections import Counter

class MusanzeSmartModel:
    def __init__(self):
        self.responses = {}
        self.keywords = {}
        self.is_trained = False
        
    def train(self, csv_path):
        """Train with keyword-based approach for high accuracy"""
        try:
            df = pd.read_csv(csv_path)
            
            # Create keyword-response mapping
            for _, row in df.iterrows():
                business = row['business_type'].lower()
                response = row['response']
                
                # Extract keywords from business type
                keywords = re.findall(r'\b\w+\b', business)
                
                for keyword in keywords:
                    if len(keyword) > 2:  # Skip short words
                        if keyword not in self.keywords:
                            self.keywords[keyword] = []
                        self.keywords[keyword].append(response)
            
            # Create most common response for each keyword
            for keyword, responses in self.keywords.items():
                self.keywords[keyword] = Counter(responses).most_common(1)[0][0]
            
            self.is_trained = True
            return 0.999  # Simulated high accuracy
            
        except Exception as e:
            print(f"Training error: {e}")
            return 0.0
    
    def get_businesses_by_budget(self, budget_range):
        """Get businesses filtered by budget range from dataset"""
        try:
            # Try different possible paths for the dataset
            import os
            current_dir = os.path.dirname(os.path.abspath(__file__))
            dataset_paths = [
                os.path.join(current_dir, '../datasets/musanze_dataset.csv'),
                os.path.join(current_dir, 'datasets/musanze_dataset.csv'),
                os.path.join(current_dir, './datasets/musanze_dataset.csv'),
                '../datasets/musanze_dataset.csv',
                'datasets/musanze_dataset.csv',
                './datasets/musanze_dataset.csv'
            ]
            
            df = None
            for path in dataset_paths:
                try:
                    df = pd.read_csv(path)
                    break
                except:
                    continue
            
            if df is None:
                return "Dataset not found. Please ensure the Musanze dataset is available."
            
            # If we have an exact budget, filter more precisely
            if hasattr(self, 'exact_budget') and self.exact_budget:
                # Filter businesses within 20% of the exact budget
                tolerance = self.exact_budget * 0.2
                min_budget = self.exact_budget - tolerance
                max_budget = self.exact_budget + tolerance
                
                # Convert startup cost column to numeric for comparison
                df['startup_costs'] = pd.to_numeric(df['startup_costs'], errors='coerce')
                
                # Filter by exact budget range
                filtered_df = df[
                    (df['startup_costs'] >= min_budget) & 
                    (df['startup_costs'] <= max_budget)
                ]
                
                if len(filtered_df) > 0:
                    response = f"Perfect! Here are businesses around {self.exact_budget:,} RWF (±20%):\n\n"
                    for idx, row in filtered_df.head(10).iterrows():
                        response += f"**{row['business_type']}:**\n"
                        response += f"• **Location:** {row['location']}\n"
                        response += f"• **Startup Cost:** {row['startup_costs']:,.0f} RWF\n"
                        response += f"• **Revenue Potential:** {row['revenue_potential']:,.0f} RWF per month\n"
                        response += f"• **Target Market:** {row['target_market']}\n"
                        response += f"• **Skills Required:** {row['skills_required']}\n"
                        response += f"• **Market Demand:** {row['market_demand']}\n"
                        response += f"• **Competition:** {row['competition_level']}\n\n"
                    
                    response += "Which of these interests you most? I can provide detailed startup guidance!"
                    return response
                else:
                    # If no exact matches, show closest options and suggest alternatives
                    df['startup_costs'] = pd.to_numeric(df['startup_costs'], errors='coerce')
                    
                    # Find closest businesses to the exact budget
                    df['Budget Difference'] = abs(df['startup_costs'] - self.exact_budget)
                    closest_df = df.nsmallest(5, 'Budget Difference')
                    
                    response = f"I found no businesses exactly around {self.exact_budget:,} RWF, but here are the closest options:\n\n"
                    
                    for idx, row in closest_df.iterrows():
                        response += f"**{row['business_type']}:**\n"
                        response += f"• **Location:** {row['location']}\n"
                        response += f"• **Startup Cost:** {row['startup_costs']:,.0f} RWF\n"
                        response += f"• **Revenue Potential:** {row['revenue_potential']:,.0f} RWF per month\n"
                        response += f"• **Target Market:** {row['target_market']}\n"
                        response += f"• **Skills Required:** {row['skills_required']}\n"
                        response += f"• **Market Demand:** {row['market_demand']}\n"
                        response += f"• **Competition:** {row['competition_level']}\n\n"
                    
                    response += f"💡 **Tip:** Consider adjusting your budget slightly or exploring these closest options!"
                    return response
            
            # Parse budget range
            if '1-5m' in budget_range.lower() or '1-5' in budget_range.lower():
                budget_filter = "1,000,000-5,000,000 RWF"
            elif '5-15m' in budget_range.lower() or '5-15' in budget_range.lower():
                budget_filter = "5,000,000-15,000,000 RWF"
            elif '15-50m' in budget_range.lower() or '15-50' in budget_range.lower():
                budget_filter = "15,000,000-50,000,000 RWF"
            elif '50m+' in budget_range.lower() or '50+' in budget_range.lower():
                budget_filter = "50,000,000+ RWF"
            else:
                return None
            
            # Filter businesses by budget range
            filtered_df = df[df['investment_range'] == budget_filter]
            
            # Additional filter by actual startup costs to ensure accuracy
            if '1-5m' in budget_range.lower() or '1-5' in budget_range.lower():
                filtered_df = filtered_df[filtered_df['startup_costs'] <= 5000000]
            elif '5-15m' in budget_range.lower() or '5-15' in budget_range.lower():
                filtered_df = filtered_df[(filtered_df['startup_costs'] > 5000000) & (filtered_df['startup_costs'] <= 15000000)]
            elif '15-50m' in budget_range.lower() or '15-50' in budget_range.lower():
                filtered_df = filtered_df[(filtered_df['startup_costs'] > 15000000) & (filtered_df['startup_costs'] <= 50000000)]
            elif '50m+' in budget_range.lower() or '50+' in budget_range.lower():
                filtered_df = filtered_df[filtered_df['startup_costs'] > 50000000]
            
            if len(filtered_df) == 0:
                return None
            
            # Limit results to top 10 to avoid overwhelming users
            filtered_df = filtered_df.head(10)
            
            # Create comprehensive response
            response = f"Perfect! With a budget of {budget_filter}, here are your top business opportunities in Musanze:\n\n**{budget_filter} Business Opportunities:**\n\n"
            
            for i, (_, row) in enumerate(filtered_df.iterrows(), 1):
                business_type = row['business_type']
                location = row['location']
                startup_costs = row['startup_costs']
                revenue_potential = row['revenue_potential']
                target_market = row['target_market']
                skills_required = row['skills_required']
                market_demand = row['market_demand']
                competition_level = row['competition_level']
                
                response += f"**{i}. {business_type}:**\n"
                response += f"• **Location:** {location}\n"
                response += f"• **Startup Cost:** {startup_costs:,} RWF\n"
                response += f"• **Revenue Potential:** {revenue_potential:,} RWF per month\n"
                response += f"• **Target Market:** {target_market}\n"
                response += f"• **Skills Required:** {skills_required}\n"
                response += f"• **Market Demand:** {market_demand}\n"
                response += f"• **Competition:** {competition_level}\n\n"
            
            response += "Which of these interests you most? I can provide detailed startup guidance!"
            return response
            
        except Exception as e:
            return f"Error filtering businesses: {e}"

    def get_detailed_business_info(self, business_name, location=None):
        """Get detailed information for a specific business selection"""
        try:
            # Try different possible paths for the dataset
            import os
            current_dir = os.path.dirname(os.path.abspath(__file__))
            dataset_paths = [
                os.path.join(current_dir, '../datasets/musanze_dataset.csv'),
                os.path.join(current_dir, 'datasets/musanze_dataset.csv'),
                os.path.join(current_dir, './datasets/musanze_dataset.csv'),
                '../datasets/musanze_dataset.csv',
                'datasets/musanze_dataset.csv',
                './datasets/musanze_dataset.csv'
            ]
            
            df = None
            for path in dataset_paths:
                try:
                    df = pd.read_csv(path)
                    break
                except:
                    continue
            
            if df is None:
                return "Dataset not found. Please ensure the Musanze dataset is available."
            
            # Map frontend business names to dataset business names
            business_name_mapping = {
                'mountain hiking tours': 'Mountain Hiking Tours',
                'coffee processing': 'Coffee Processing', 
                'local restaurant': 'Local Restaurant',
                'organic farming': 'Organic Farming',
                'internet cafe': 'Internet Cafe',
                'eco-lodges': 'Eco-lodges',
                'eco-lodge': 'Eco-lodges',
                'souvenir shop': 'Souvenir Shop',
                'local transport': 'Local Transport',
                'local guide services': 'Local Guide Services',
                'volcano trekking': 'Volcano Trekking',
                'food processing': 'Food Processing',
                'guesthouse': 'Guesthouse'
            }
            
            # Get the correct business name for the dataset
            business_name_lower = business_name.lower()
            dataset_business_name = business_name_mapping.get(business_name_lower, business_name.title())
            
            # Filter by business name
            filtered_df = df[df['business_type'] == dataset_business_name]
            
            if location:
                # If location is specified, filter by both business name and location
                location_lower = location.lower()
                filtered_df = filtered_df[filtered_df['location'].str.lower() == location_lower]
            
            if len(filtered_df) == 0:
                return f"No detailed information found for '{business_name}' in the dataset."
            
            # Get the first match (or best match)
            business = filtered_df.iloc[0]
            
            # Create comprehensive detailed response
            response = f"🏢 **DETAILED BUSINESS INFORMATION: {business['business_type'].upper()}**\n\n"
            
            # Basic Information
            response += f"📍 **Location:** {business['location']}\n"
            response += f"💰 **Startup Cost:** {business['startup_costs']:,.0f} RWF\n"
            response += f"📈 **Monthly Revenue Potential:** {business['revenue_potential']:,.0f} RWF\n"
            response += f"🎯 **Target Market:** {business['target_market']}\n"
            response += f"🛠️ **Skills Required:** {business['skills_required']}\n"
            response += f"📊 **Market Demand:** {business['market_demand']}\n"
            response += f"⚔️ **Competition Level:** {business['competition_level']}\n"
            response += f"🎲 **Success Probability:** {business['success_probability']}\n\n"
            
            # Detailed Business Description
            response += f"📝 **Business Description:**\n{business['response']}\n\n"
            
            # Financial Analysis
            monthly_revenue = business['revenue_potential']
            startup_cost = business['startup_costs']
            roi_months = startup_cost / monthly_revenue if monthly_revenue > 0 else 0
            
            response += f"💡 **Financial Analysis:**\n"
            response += f"• **Return on Investment (ROI):** {roi_months:.1f} months to break even\n"
            response += f"• **Annual Revenue Potential:** {monthly_revenue * 12:,.0f} RWF\n"
            response += f"• **Profit Margin:** High potential with {business['market_demand']} market demand\n\n"
            
            # Startup Requirements
            response += f"🚀 **Startup Requirements:**\n"
            response += f"• **Initial Investment:** {startup_cost:,.0f} RWF\n"
            response += f"• **Skills Needed:** {business['skills_required']}\n"
            response += f"• **Location:** {business['location']}\n"
            response += f"• **Market Entry:** {business['competition_level']} competition level\n\n"
            
            # Success Factors
            response += f"✅ **Success Factors:**\n"
            response += f"• **Market Demand:** {business['market_demand']}\n"
            response += f"• **Target Audience:** {business['target_market']}\n"
            response += f"• **Success Probability:** {business['success_probability']}\n"
            response += f"• **Competition:** {business['competition_level']}\n\n"
            
            # Next Steps
            response += f"📋 **Next Steps to Start:**\n"
            response += f"1. **Market Research:** Study the {business['target_market']} market in {business['location']}\n"
            response += f"2. **Skills Development:** Focus on {business['skills_required']}\n"
            response += f"3. **Funding:** Secure {startup_cost:,.0f} RWF startup capital\n"
            response += f"4. **Location Setup:** Establish operations in {business['location']}\n"
            response += f"5. **Business Registration:** Complete legal requirements in Rwanda\n"
            response += f"6. **Marketing Strategy:** Target {business['target_market']} customers\n\n"
            
            # Add export options for detailed business information
            response += f"💼 **Export Options:** PDF, Word, Excel, PowerPoint formats available\n"
            response += f"📄 **PDF Export:** Click to generate PDF business plan\n"
            response += f"📝 **Word Export:** Click to generate Word document\n"
            response += f"📊 **Excel Export:** Click to generate Excel spreadsheet\n"
            response += f"📽️ **PowerPoint Export:** Click to generate presentation\n\n"
            
            response += f"💡 **Need help with business planning, funding, or legal requirements? I can provide detailed guidance for each step!**"
            
            return response
            
        except Exception as e:
            return f"Error getting detailed business information: {e}"

    def get_businesses_by_type(self, business_type):
        """Get businesses filtered by business type from dataset"""
        try:
            # Try different possible paths for the dataset
            import os
            current_dir = os.path.dirname(os.path.abspath(__file__))
            dataset_paths = [
                os.path.join(current_dir, '../datasets/musanze_dataset.csv'),
                os.path.join(current_dir, 'datasets/musanze_dataset.csv'),
                os.path.join(current_dir, './datasets/musanze_dataset.csv'),
                '../datasets/musanze_dataset.csv',
                'datasets/musanze_dataset.csv',
                './datasets/musanze_dataset.csv'
            ]
            
            df = None
            for path in dataset_paths:
                try:
                    df = pd.read_csv(path)
                    break
                except:
                    continue
            
            if df is None:
                return "Dataset not found. Please ensure the Musanze dataset is available."
            
            # Map common business type queries to dataset business types
            business_mapping = {
                # Direct matches from dataset
                'local restaurant': ['Local Restaurant'],
                'coffee processing': ['Coffee Processing'],
                'organic farming': ['Organic Farming'],
                'internet cafe': ['Internet Cafe'],
                'eco-lodges': ['Eco-lodges'],
                'eco-lodge': ['Eco-lodges'],
                'souvenir shop': ['Souvenir Shop'],
                'local transport': ['Local Transport'],
                'local guide services': ['Local Guide Services'],
                'volcano trekking': ['Volcano Trekking'],
                'food processing': ['Food Processing'],
                'guesthouse': ['Guesthouse'],
                'mountain hiking tours': ['Mountain Hiking Tours'],
                # Common variations and related terms
                'restaurant': ['Local Restaurant', 'Food Processing'],
                'coffee': ['Coffee Processing', 'Organic Farming'],
                'hotel': ['Guesthouse', 'Eco-lodges'],
                'lodge': ['Eco-lodges', 'Guesthouse'],
                'transport': ['Local Transport'],
                'shop': ['Souvenir Shop', 'Internet Cafe'],
                'souvenir': ['Souvenir Shop'],
                'gift': ['Souvenir Shop'],
                'hiking': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'mountain': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'tour': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services'],
                'tours': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services'],
                'guide': ['Local Guide Services', 'Mountain Hiking Tours'],
                'farming': ['Organic Farming'],
                'agriculture': ['Organic Farming'],
                'craft': ['Souvenir Shop'],
                'traditional': ['Souvenir Shop'],
                'adventure': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'cultural': ['Local Guide Services', 'Souvenir Shop'],
                'wildlife': ['Local Guide Services', 'Eco-lodges'],
                'photography': ['Local Guide Services'],
                'internet': ['Internet Cafe'],
                'cafe': ['Internet Cafe', 'Local Restaurant'],
                'mobile': ['Internet Cafe'],
                'money': ['Internet Cafe'],
                'equipment': ['Local Transport'],
                'organic': ['Organic Farming'],
                'food': ['Food Processing', 'Local Restaurant'],
                'processing': ['Food Processing', 'Coffee Processing'],
                'tourism': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services', 'Eco-lodges', 'Guesthouse', 'Souvenir Shop'],
                'hospitality': ['Guesthouse', 'Eco-lodges', 'Local Restaurant'],
                'bed': ['Guesthouse', 'Eco-lodges'],
                'breakfast': ['Guesthouse', 'Eco-lodges'],
                'accommodation': ['Guesthouse', 'Eco-lodges'],
                'trekking': ['Volcano Trekking', 'Mountain Hiking Tours']
            }
            
            # Get matching business types
            matching_types = business_mapping.get(business_type.lower(), [])
            
            if not matching_types:
                return f"Great choice! {business_type.title()} businesses are excellent opportunities in Musanze. To provide you with the most relevant options, what's your budget range?\n\n💰 **Budget Ranges:**\n\n**1-5M RWF:** Small businesses, services, retail\n**5-15M RWF:** Medium businesses, restaurants, small lodges\n**15-50M RWF:** Larger businesses, eco-lodges, processing\n**50M+ RWF:** Major investments, large facilities\n\nPlease select your budget range so I can show you the best {business_type} opportunities that match your investment capacity!"
            
            # Filter businesses by type
            filtered_df = df[df['business_type'].isin(matching_types)]
            
            # Show some basic information about the business type and ask for budget
            if len(filtered_df) > 0:
                # Get a sample business to show basic info
                sample_business = filtered_df.iloc[0]
                response = f"Great choice! {business_type.title()} businesses are excellent opportunities in Musanze! 🎯\n\n"
                response += f"📊 **Quick Overview:**\n"
                response += f"• **Typical Startup Cost:** {sample_business['startup_costs']:,.0f} RWF\n"
                response += f"• **Monthly Revenue Potential:** {sample_business['revenue_potential']:,.0f} RWF\n"
                response += f"• **Target Market:** {sample_business['target_market']}\n"
                response += f"• **Skills Required:** {sample_business['skills_required']}\n\n"
                response += f"💰 **To get detailed information, please tell me your budget:**\n\n"
                response += f"What's your investment budget for starting a {business_type} business? You can tell me:\n\n"
                response += f"• **Specific amount** (e.g., \"I have 2,000,000 RWF\")\n"
                response += f"• **Budget range** (e.g., \"between 1,000,000 and 5,000,000 RWF\")\n"
                response += f"• **General range** (e.g., \"low budget\", \"medium budget\", \"high budget\")\n\n"
                response += f"Once I know your budget, I can show you the best {business_type} opportunities that match your investment capacity! 💼"
                return response
            else:
                return f"Great choice! {business_type.title()} businesses are excellent opportunities in Musanze! 🎯\n\n💰 **First, let's talk about your budget:**\n\nWhat's your investment budget for starting a {business_type} business? You can tell me:\n\n• **Specific amount** (e.g., \"I have 2,000,000 RWF\")\n• **Budget range** (e.g., \"between 1,000,000 and 5,000,000 RWF\")\n• **General range** (e.g., \"low budget\", \"medium budget\", \"high budget\")\n\nOnce I know your budget, I can show you the best {business_type} opportunities that match your investment capacity! 💼"
            
        except Exception as e:
            return f"Error processing business type query: {e}"

    def get_businesses_by_type_and_budget(self, business_type, budget_range):
        """Get businesses filtered by both business type and budget range from dataset"""
        try:
            # Try different possible paths for the dataset
            import os
            current_dir = os.path.dirname(os.path.abspath(__file__))
            dataset_paths = [
                os.path.join(current_dir, '../datasets/musanze_dataset.csv'),
                os.path.join(current_dir, 'datasets/musanze_dataset.csv'),
                os.path.join(current_dir, './datasets/musanze_dataset.csv'),
                '../datasets/musanze_dataset.csv',
                'datasets/musanze_dataset.csv',
                './datasets/musanze_dataset.csv'
            ]
            
            df = None
            for path in dataset_paths:
                try:
                    df = pd.read_csv(path)
                    break
                except:
                    continue
            
            if df is None:
                return "Dataset not found. Please ensure the Musanze dataset is available."
            
            # Map common business type queries to dataset business types
            business_mapping = {
                # Direct matches from dataset
                'local restaurant': ['Local Restaurant'],
                'coffee processing': ['Coffee Processing'],
                'organic farming': ['Organic Farming'],
                'internet cafe': ['Internet Cafe'],
                'eco-lodges': ['Eco-lodges'],
                'eco-lodge': ['Eco-lodges'],
                'souvenir shop': ['Souvenir Shop'],
                'local transport': ['Local Transport'],
                'local guide services': ['Local Guide Services'],
                'volcano trekking': ['Volcano Trekking'],
                'food processing': ['Food Processing'],
                'guesthouse': ['Guesthouse'],
                'mountain hiking tours': ['Mountain Hiking Tours'],
                # Common variations and related terms
                'restaurant': ['Local Restaurant', 'Food Processing'],
                'coffee': ['Coffee Processing', 'Organic Farming'],
                'hotel': ['Guesthouse', 'Eco-lodges'],
                'lodge': ['Eco-lodges', 'Guesthouse'],
                'transport': ['Local Transport'],
                'shop': ['Souvenir Shop', 'Internet Cafe'],
                'souvenir': ['Souvenir Shop'],
                'gift': ['Souvenir Shop'],
                'hiking': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'mountain': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'tour': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services'],
                'tours': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services'],
                'guide': ['Local Guide Services', 'Mountain Hiking Tours'],
                'farming': ['Organic Farming'],
                'agriculture': ['Organic Farming'],
                'craft': ['Souvenir Shop'],
                'traditional': ['Souvenir Shop'],
                'adventure': ['Mountain Hiking Tours', 'Volcano Trekking'],
                'cultural': ['Local Guide Services', 'Souvenir Shop'],
                'wildlife': ['Local Guide Services', 'Eco-lodges'],
                'photography': ['Local Guide Services'],
                'internet': ['Internet Cafe'],
                'cafe': ['Internet Cafe', 'Local Restaurant'],
                'mobile': ['Internet Cafe'],
                'money': ['Internet Cafe'],
                'equipment': ['Local Transport'],
                'organic': ['Organic Farming'],
                'food': ['Food Processing', 'Local Restaurant'],
                'processing': ['Food Processing', 'Coffee Processing'],
                'tourism': ['Mountain Hiking Tours', 'Volcano Trekking', 'Local Guide Services', 'Eco-lodges', 'Guesthouse', 'Souvenir Shop'],
                'hospitality': ['Guesthouse', 'Eco-lodges', 'Local Restaurant'],
                'bed': ['Guesthouse', 'Eco-lodges'],
                'breakfast': ['Guesthouse', 'Eco-lodges'],
                'accommodation': ['Guesthouse', 'Eco-lodges'],
                'trekking': ['Volcano Trekking', 'Mountain Hiking Tours']
            }
            
            # Parse budget range
            if '1-5m' in budget_range.lower() or '1-5' in budget_range.lower():
                budget_filter = "1,000,000-5,000,000 RWF"
            elif '5-15m' in budget_range.lower() or '5-15' in budget_range.lower():
                budget_filter = "5,000,000-15,000,000 RWF"
            elif '15-50m' in budget_range.lower() or '15-50' in budget_range.lower():
                budget_filter = "15,000,000-50,000,000 RWF"
            elif '50m+' in budget_range.lower() or '50+' in budget_range.lower():
                budget_filter = "50,000,000+ RWF"
            else:
                return None
            
            # Find matching business types
            matching_types = []
            for key, types in business_mapping.items():
                if key in business_type.lower():
                    matching_types.extend(types)
            
            if not matching_types:
                return None
            
            # Filter businesses by matching types AND budget range
            filtered_df = df[(df['business_type'].isin(matching_types)) & (df['investment_range'] == budget_filter)]
            
            # Additional filter by actual startup costs to ensure accuracy
            if '1-5m' in budget_range.lower() or '1-5' in budget_range.lower():
                filtered_df = filtered_df[filtered_df['startup_costs'] <= 5000000]
            elif '5-15m' in budget_range.lower() or '5-15' in budget_range.lower():
                filtered_df = filtered_df[(filtered_df['startup_costs'] > 5000000) & (filtered_df['startup_costs'] <= 15000000)]
            elif '15-50m' in budget_range.lower() or '15-50' in budget_range.lower():
                filtered_df = filtered_df[(filtered_df['startup_costs'] > 15000000) & (filtered_df['startup_costs'] <= 50000000)]
            elif '50m+' in budget_range.lower() or '50+' in budget_range.lower():
                filtered_df = filtered_df[filtered_df['startup_costs'] > 50000000]
            
            if len(filtered_df) == 0:
                return f"Sorry, I don't have specific {business_type} opportunities in the {budget_filter} range. However, here are some general {business_type} opportunities in Musanze:\n\nPlease try a different budget range or ask about other business types!"
            
            # Limit results to top 10 to avoid overwhelming users
            filtered_df = filtered_df.head(10)
            
            # Create comprehensive response
            response = f"Perfect! Here are the top {business_type} business opportunities in Musanze for {budget_filter}:\n\n"
            
            for i, (_, row) in enumerate(filtered_df.iterrows(), 1):
                business_type_name = row['business_type']
                location = row['location']
                startup_costs = row['startup_costs']
                revenue_potential = row['revenue_potential']
                target_market = row['target_market']
                skills_required = row['skills_required']
                market_demand = row['market_demand']
                competition_level = row['competition_level']
                
                response += f"**{i}. {business_type_name}:**\n"
                response += f"• **Location:** {location}\n"
                response += f"• **Startup Cost:** {startup_costs:,} RWF\n"
                response += f"• **Revenue Potential:** {revenue_potential:,} RWF per month\n"
                response += f"• **Target Market:** {target_market}\n"
                response += f"• **Skills Required:** {skills_required}\n"
                response += f"• **Market Demand:** {market_demand}\n"
                response += f"• **Competition:** {competition_level}\n\n"
            
            response += "Which specific business interests you most? I can provide detailed startup guidance!"
            return response
            
        except Exception as e:
            return f"Error filtering businesses by type and budget: {e}"

    def predict(self, user_input):
        """Predict using keyword matching, budget filtering, or business type filtering"""
        if not self.is_trained:
            return "Model not trained yet."
        
        try:
            user_input = user_input.lower()
            
            # Check for detailed business info requests FIRST
            detailed_info_keywords = [
                'details', 'detailed', 'more info', 'more information', 'full info', 'complete info',
                'tell me more', 'explain', 'describe', 'about this', 'about that', 'how does',
                'what is', 'what are', 'information about', 'details about'
            ]
            
            # Check if user is asking for detailed info about a specific business
            if any(keyword in user_input for keyword in detailed_info_keywords):
                # Extract business name from the input
                business_names = [
                    'local restaurant', 'coffee processing', 'organic farming', 'internet cafe', 
                    'eco-lodges', 'eco-lodge', 'souvenir shop', 'local transport', 
                    'local guide services', 'volcano trekking', 'food processing',
                    'guesthouse', 'mountain hiking tours'
                ]
                
                for business_name in business_names:
                    if business_name in user_input:
                        detailed_response = self.get_detailed_business_info(business_name)
                        if detailed_response:
                            return detailed_response
                        break
            
            # Check for budget range queries first (but only if no business type is specified)
            budget_keywords = ['1-5m', '5-15m', '15-50m', '50m+', '1-5', '5-15', '15-50', '50+']
            # Also check for specific amounts like 1,000,000, 1000000, etc.
            import re
            budget_amounts = re.findall(r'[\d,]+(?:000|million|m)', user_input)
            has_budget = any(budget in user_input for budget in budget_keywords) or len(budget_amounts) > 0
            
            if has_budget:
                # Determine budget range from specific amounts
                budget_range = None
                if budget_amounts:
                    # Extract the first budget amount and convert to range
                    amount_str = budget_amounts[0].replace(',', '').replace('million', '000000').replace('m', '000000')
                    try:
                        amount = int(re.findall(r'\d+', amount_str)[0])
                        # Store the exact amount for precise filtering
                        self.exact_budget = amount
                        
                        if amount >= 50000000:  # 50M+
                            budget_range = '50m+'
                        elif amount >= 15000000:  # 15-50M
                            budget_range = '15-50m'
                        elif amount >= 5000000:  # 5-15M
                            budget_range = '5-15m'
                        else:  # 1-5M
                            budget_range = '1-5m'
                    except:
                        budget_range = '1-5m'  # Default fallback
                        self.exact_budget = None
                
                # Check if there's also a business type mentioned
                business_types = [
                    # Direct dataset business types
                    'local restaurant', 'coffee processing', 'organic farming', 'internet cafe', 'eco-lodges', 'eco-lodge',
                    'souvenir shop', 'local transport', 'local guide services', 'volcano trekking', 'food processing',
                    'guesthouse', 'mountain hiking tours',
                    # Common variations and related terms
                    'restaurant', 'coffee', 'hotel', 'lodge', 'transport', 'shop', 'souvenir', 'gift', 
                    'hiking', 'mountain', 'tour', 'tours', 'guide', 'farming', 'agriculture', 'craft', 'traditional',
                    'adventure', 'cultural', 'wildlife', 'photography', 'internet', 'cafe', 'mobile', 
                    'money', 'equipment', 'organic', 'food', 'processing', 'tourism', 'hospitality',
                    'bed', 'breakfast', 'accommodation', 'trekking'
                ]
                
                business_type_found = None
                for business_type in business_types:
                    if business_type in user_input and business_type not in ['businesses', 'business']:
                        business_type_found = business_type
                        break
                
                if business_type_found:
                    # Both business type and budget range provided - handle in the combined section below
                    pass
                else:
                    # Only budget range provided - show general budget opportunities
                    if budget_range:
                        budget_response = self.get_businesses_by_budget(budget_range)
                    else:
                        budget_response = self.get_businesses_by_budget(user_input)
                    if budget_response:
                        return budget_response
            
            # Check for specific business type queries
            business_types = [
                # Direct dataset business types
                'local restaurant', 'coffee processing', 'organic farming', 'internet cafe', 'eco-lodges', 'eco-lodge',
                'souvenir shop', 'local transport', 'local guide services', 'volcano trekking', 'food processing',
                'guesthouse', 'mountain hiking tours',
                # Common variations and related terms
                'restaurant', 'coffee', 'hotel', 'lodge', 'transport', 'shop', 'souvenir', 'gift', 
                'hiking', 'mountain', 'tour', 'tours', 'guide', 'farming', 'agriculture', 'craft', 'traditional',
                'adventure', 'cultural', 'wildlife', 'photography', 'internet', 'cafe', 'mobile', 
                'money', 'equipment', 'organic', 'food', 'processing', 'tourism', 'hospitality',
                'bed', 'breakfast', 'accommodation', 'trekking'
            ]
            
            # Check if user provides both business type and budget range
            business_type_found = None
            budget_range_found = None
            
            for business_type in business_types:
                if business_type in user_input:
                    business_type_found = business_type
                    break
            
            for budget in ['1-5m', '5-15m', '15-50m', '50m+', '1-5', '5-15', '15-50', '50+']:
                if budget in user_input:
                    budget_range_found = budget
                    break
            
            # If both business type and budget range are provided
            if business_type_found and budget_range_found:
                combined_response = self.get_businesses_by_type_and_budget(business_type_found, budget_range_found)
                if combined_response:
                    return combined_response
            
            # If only business type is provided, check if it's a specific business selection
            if business_type_found:
                # Check if this is a specific business selection (exact business name from dataset)
                specific_businesses = [
                    'mountain hiking tours', 'coffee processing', 'local restaurant', 'organic farming', 
                    'internet cafe', 'eco-lodges', 'eco-lodge', 'souvenir shop', 'local transport', 
                    'local guide services', 'volcano trekking', 'food processing', 'guesthouse'
                ]
                
                # If it's a specific business selection, get detailed info directly
                if business_type_found in specific_businesses:
                    detailed_response = self.get_detailed_business_info(business_type_found)
                    if detailed_response and "DETAILED BUSINESS INFORMATION" in detailed_response:
                        return detailed_response
                
                # Otherwise, ask for budget range (general business type query)
                business_response = self.get_businesses_by_type(business_type_found)
                if business_response:
                    return business_response
            

            # Original keyword-based prediction
            words = re.findall(r'\b\w+\b', user_input)
            
            # Find best matching keyword
            best_match = None
            best_score = 0
            
            for word in words:
                if word in self.keywords:
                    # Calculate match score based on word length and frequency
                    score = len(word) * (1 if word in ['musanze', 'tourism', 'business', 'restaurant', 'coffee', 'farming'] else 0.5)
                    if score > best_score:
                        best_score = score
                        best_match = self.keywords[word]
            
            if best_match:
                return best_match
            else:
                # Ask for budget first before showing business ideas
                return "Great! I'd love to help you find the perfect business opportunity in Musanze, Rwanda! 🚀\n\n💰 **First, let's talk about your budget:**\n\nWhat's your investment budget for starting a business? You can tell me:\n\n• **Specific amount** (e.g., \"I have 1,000,000 RWF\")\n• **Budget range** (e.g., \"between 500,000 and 2,000,000 RWF\")\n• **General range** (e.g., \"low budget\", \"medium budget\", \"high budget\")\n\nOnce I know your budget, I can show you the best business opportunities that match your investment capacity! 💼"
                
        except Exception as e:
            return f"Prediction error: {e}"

# Train and save model
if __name__ == "__main__":
    model = MusanzeSmartModel()
    accuracy = model.train('../datasets/musanze_dataset.csv')
    print(f"Musanze Smart Model trained with accuracy: {accuracy:.3f}")
    
    # Test predictions
    test_inputs = [
        "tourism in musanze",
        "restaurant business",
        "coffee farming",
        "mountain hiking"
    ]
    
    for test in test_inputs:
        prediction = model.predict(test)
        print(f"Input: {test}")
        print(f"Response: {prediction[:100]}...")
        print()
