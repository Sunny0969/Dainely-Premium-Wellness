-- =============================================
-- DAINELY WALLET APP - DATABASE SCHEMA
-- Version: 2.0 (Enhanced with Marketing & Compliance)
-- Date: December 202
-- =============================================

-- =============================================
-- 1. USERS TABLE (Enhanced)
-- =============================================
CREATE TABLE users (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password_hash TEXT,
    language_preference TEXT DEFAULT 'en' CHECK (language_preference IN ('en', 'es')),
    is_active BOOLEAN DEFAULT true,
    is_admin BOOLEAN DEFAULT false,
    push_token TEXT,
    push_platform TEXT CHECK (push_platform IN ('ios', 'android', 'web')),
    referral_code TEXT UNIQUE,
    referred_by UUID REFERENCES users(id), -- NEW: Track who referred this user
    signup_source TEXT, -- NEW: Track where user signed up ('meta_ad', 'qr_campaign', 'organic', 'referral', 'oauth_google', 'oauth_facebook', 'oauth_apple')
    signup_campaign_id UUID, -- NEW: Link to specific campaign
    utm_source TEXT, -- NEW: UTM parameters for attribution
    utm_medium TEXT,
    utm_campaign TEXT,
    utm_term TEXT,
    utm_content TEXT,
    meta_ad_id TEXT, -- NEW: Meta Ad ID for direct attribution
    supabase_auth_id UUID, -- NEW: Link to auth.users for OAuth
    email_verified BOOLEAN DEFAULT false, -- NEW: Email verification status
    last_login TIMESTAMPTZ,
    last_logout TIMESTAMPTZ,
    status_updated_at TIMESTAMPTZ,
    status_updated_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_referral_code ON users(referral_code);
CREATE INDEX idx_users_referred_by ON users(referred_by);
CREATE INDEX idx_users_signup_source ON users(signup_source);

-- =============================================
-- 2. SOCIAL_AUTH (No changes needed)
-- =============================================
CREATE TABLE social_auth (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider TEXT NOT NULL CHECK (provider IN ('google', 'facebook', 'apple')),
    provider_user_id TEXT NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    created_at TIMESTAMPTZ DEFAULT now(),
    UNIQUE(provider, provider_user_id)
);

-- =============================================
-- 3. USER_PROFILES (Enhanced with validation)
-- =============================================
CREATE TABLE user_profiles (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    first_name TEXT,
    last_name TEXT,
    gender TEXT CHECK (gender IN ('male', 'female', 'other', 'prefer_not_to_say')),
    height_cm INTEGER CHECK (height_cm BETWEEN 50 AND 300),
    weight_kg NUMERIC(5,2) CHECK (weight_kg BETWEEN 10 AND 500),
    birth_date DATE CHECK (birth_date > '1900-01-01'),
    timezone TEXT DEFAULT 'America/New_York',
    profile_image_url TEXT, -- NEW: User profile image
    phone_number TEXT, -- NEW: For SMS notifications
    email_verified BOOLEAN DEFAULT false, -- NEW: Track email verification
    phone_verified BOOLEAN DEFAULT false, -- NEW: Track phone verification
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 4. HEALTH_PROFILES (Enhanced for compliance)
-- =============================================
CREATE TABLE health_profiles (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    health_conditions_encrypted TEXT,
    medications_encrypted TEXT,
    allergies_encrypted TEXT,
    emergency_contact_encrypted TEXT,
    insurance_info_encrypted TEXT,
    data_consent_given BOOLEAN DEFAULT false, -- NEW: GDPR/CCPA compliance
    consent_given_at TIMESTAMPTZ, -- NEW: When consent was given
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    -- Add check to ensure consent if data exists
    CONSTRAINT consent_required CHECK (
        (
            health_conditions_encrypted IS NULL AND 
            medications_encrypted IS NULL AND 
            allergies_encrypted IS NULL AND 
            emergency_contact_encrypted IS NULL AND 
            insurance_info_encrypted IS NULL
        ) OR data_consent_given = true
    )
);

-- =============================================
-- NEW: SHOPIFY STORES TABLE (Minimal, only 6 columns)
-- =============================================
CREATE TABLE stores (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    shopify_domain TEXT UNIQUE NOT NULL,
    store_name TEXT NOT NULL,
    currency TEXT DEFAULT 'USD',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT now()
);

-- Insert main stores here (run this after table creation)
INSERT INTO stores (shopify_domain, store_name, currency) VALUES ('ididit555.myshopify.com', 'Dainely Wellness', 'USD');
INSERT INTO stores (shopify_domain, store_name, currency) VALUES ('dmede-usa.myshopify.com', 'DMEDE Comfort Wear', 'USD');

-- =============================================
-- 5. LOYALTY_POINTS (Enhanced with expiration and store ID)
-- =============================================
CREATE TABLE loyalty_points (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    points_balance INTEGER NOT NULL DEFAULT 0 CHECK (points_balance >= 0),
    points_earned_total INTEGER NOT NULL DEFAULT 0,
    points_redeemed_total INTEGER NOT NULL DEFAULT 0,
    tier TEXT NOT NULL DEFAULT 'bronze' CHECK (tier IN ('bronze', 'silver', 'gold')),
    points_expire_at TIMESTAMPTZ, -- NEW: When points will expire
    next_tier_progress INTEGER DEFAULT 0, -- NEW: Progress to next tier (0-100)
    last_updated TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_loyalty_points_user_id ON loyalty_points(user_id);

-- =============================================
-- 6. POINT_TRANSACTIONS (Enhanced with expiration tracking)
-- =============================================
CREATE TABLE point_transactions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
	store_id UUID REFERENCES stores(id),
    points INTEGER NOT NULL,
    transaction_type TEXT NOT NULL CHECK (transaction_type IN (
        'signup_bonus', 
        'referral_bonus',
        'referral_signup', -- NEW: Bonus for referring someone who signs up
        'qr_scan',
        'wallet_pass_install',
        'purchase',
        'redemption',
        'expiration',
        'admin_adjustment',
        'tier_bonus', -- NEW: Bonus for tier upgrades
        'manual' -- NEW: Manual admin adjustment
    )),
    description TEXT,
    meta_data JSONB DEFAULT '{}',
    expires_at TIMESTAMPTZ, -- NEW: When this specific transaction's points expire
    parent_transaction_id UUID REFERENCES point_transactions(id), -- NEW: Link related transactions
    created_at TIMESTAMPTZ DEFAULT now()
);

-- Index for user transaction history
CREATE INDEX idx_point_transactions_user_id ON point_transactions(user_id);
CREATE INDEX idx_point_transactions_user_id_created ON point_transactions(user_id, created_at DESC);
CREATE INDEX idx_point_transactions_store_id ON point_transactions(store_id);

-- =============================================
-- 7. WALLET_PASSES (Enhanced for marketing tracking)
-- =============================================
CREATE TABLE wallet_passes (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    serial_number TEXT UNIQUE NOT NULL,
    pass_type TEXT NOT NULL CHECK (pass_type IN ('apple', 'google')),
    auth_token_hash TEXT,
    is_active BOOLEAN DEFAULT true,
    install_source TEXT, -- NEW: Where was pass installed ('qr_scan', 'email', 'direct_link')
    campaign_id UUID, -- NEW: Which campaign led to install
    device_model TEXT, -- NEW: For analytics
    install_location JSONB, -- NEW: GPS coordinates if available
    last_updated TIMESTAMPTZ DEFAULT now(),
    created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_wallet_passes_user_id ON wallet_passes(user_id);

-- =============================================
-- 8. NOTIFICATIONS (Enhanced for marketing campaigns)
-- =============================================
CREATE TABLE notifications (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    notification_type TEXT NOT NULL CHECK (notification_type IN (
        'general', 
        'promo', 
        'points', 
        'system', 
        'announcement',
        'marketing' -- NEW: For marketing campaigns
    )),
    title_en TEXT NOT NULL,
    content_en TEXT NOT NULL,
    title_es TEXT,
    content_es TEXT,
    media_url TEXT,
    audience TEXT NOT NULL CHECK (audience IN (
        'all', 
        'active', 
        'inactive', 
        'bronze', 
        'silver', 
        'gold',
        'segment' -- NEW: For custom segments
    )),
    segment_criteria JSONB DEFAULT '{}', -- NEW: Criteria for segment targeting
    send_at TIMESTAMPTZ, -- NEW: Schedule for future sending
    is_sent BOOLEAN DEFAULT false, -- NEW: Track if notification was sent
    campaign_id TEXT, -- NEW: Link to marketing campaign
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 9. USER_NOTIFICATIONS (Enhanced with sent_via tracking)
-- =============================================
CREATE TABLE user_notifications (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    notification_id UUID NOT NULL REFERENCES notifications(id) ON DELETE CASCADE,
    is_read BOOLEAN DEFAULT false,
    read_at TIMESTAMPTZ,
    sent_via TEXT CHECK (sent_via IN ('push', 'in_app', 'email', 'sms')), -- NEW: How notification was sent
    created_at TIMESTAMPTZ DEFAULT now(),
    UNIQUE(user_id, notification_id) -- Prevent duplicate notifications to same user
);

-- =============================================
-- 10. QR_CAMPAIGNS (Enhanced for ROI tracking)
-- =============================================
CREATE TABLE qr_campaigns (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
	store_id UUID REFERENCES stores(id),
    short_id TEXT UNIQUE NOT NULL,
    campaign_name TEXT NOT NULL,
    campaign_type TEXT NOT NULL CHECK (campaign_type IN (
        'wallet_install', 
        'points_bonus', 
        'product_promo', 
        'event_checkin',
        'referral' -- NEW: For referral campaigns
    )),
    points_award INTEGER NOT NULL DEFAULT 250 CHECK (points_award BETWEEN 0 AND 10000),
    qr_code_url TEXT,
    is_active BOOLEAN DEFAULT true,
    expires_at TIMESTAMPTZ,
    max_scans INTEGER,
    scan_count INTEGER DEFAULT 0,
    conversion_count INTEGER DEFAULT 0, -- NEW: How many converted to customers
    meta_ad_id TEXT,
    meta_adset_id TEXT, -- NEW: Track adset for better attribution
    meta_campaign_id TEXT, -- NEW: Track campaign ID
    utm_source TEXT, -- NEW: UTM parameters for this campaign
    utm_medium TEXT,
    utm_campaign TEXT,
    budget NUMERIC(10,2), -- NEW: Campaign budget
    cost_per_scan NUMERIC(10,2), -- NEW: Calculated cost per scan
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_by UUID REFERENCES users(id),
    updated_at TIMESTAMPTZ DEFAULT now(),
    deleted_by UUID REFERENCES users(id),
    deleted_at TIMESTAMPTZ
);
CREATE INDEX idx_qr_campaigns_store_active ON qr_campaigns(store_id, is_active);

-- =============================================
-- 11. QR_SCANS (Enhanced for analytics)
-- =============================================
CREATE TABLE qr_scans (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    campaign_id UUID NOT NULL REFERENCES qr_campaigns(id),
    user_id UUID NOT NULL REFERENCES users(id),
    scanned_at TIMESTAMPTZ DEFAULT now(),
    device_info JSONB DEFAULT '{}',
    location JSONB,
    platform TEXT CHECK (platform IN ('ios', 'android', 'web')),
    points_awarded INTEGER NOT NULL DEFAULT 0,
    ip_address INET, -- NEW: For geographic analytics
    user_agent TEXT, -- NEW: For device/browser analytics
    conversion_value NUMERIC(10,2), -- NEW: If scan led to purchase
    UNIQUE(campaign_id, user_id) -- Prevent duplicate scans
);
CREATE INDEX idx_qr_scans_scanned_at ON qr_scans(scanned_at DESC);

-- =============================================
-- 12. PRODUCTS (Enhanced for points redemption)
-- =============================================
CREATE TABLE products (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
	store_id UUID NOT NULL REFERENCES stores(id), -- Store UUID HERE
    shopify_id TEXT, -- NEW: Shopify ID must be unique per store
    title TEXT NOT NULL,
    description TEXT,
    price NUMERIC(10,2),
    points_cost INTEGER, -- NEW: How many points needed to redeem
    image_url TEXT,
    category TEXT, -- NEW: Product category for filtering
    is_featured BOOLEAN DEFAULT false, -- NEW: Featured products for promotions
    is_active BOOLEAN DEFAULT true,
    stock_quantity INTEGER, -- NEW: Track inventory for points redemption
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
	UNIQUE(store_id, shopify_id)
);
CREATE INDEX idx_products_store_id ON products(store_id);

-- =============================================
-- 13. COUPONS (Enhanced for tracking)
-- =============================================
CREATE TABLE coupons (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    store_id UUID NOT NULL REFERENCES stores(id), -- NEW: Store UUID HERE
	user_id UUID NOT NULL REFERENCES users(id),
    code TEXT UNIQUE NOT NULL,
    discount_amount NUMERIC(10,2),
    discount_type TEXT NOT NULL CHECK (discount_type IN ('fixed', 'percentage', 'free_shipping')),
    min_purchase_amount NUMERIC(10,2), -- NEW: Minimum cart value
    is_used BOOLEAN DEFAULT false,
    used_at TIMESTAMPTZ, -- NEW: When coupon was used
    used_order_id TEXT, -- NEW: Shopify order ID
    expires_at TIMESTAMPTZ,
    points_used INTEGER, -- NEW: How many points were used for this coupon
    product_id UUID REFERENCES products(id), -- NEW: If coupon is for specific product
    campaign_id UUID REFERENCES qr_campaigns(id), -- NEW: Attribution to QR campaign
    attribution_window_days INTEGER DEFAULT 30, -- NEW: Days to attribute purchase to campaign
    created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_coupons_user_id ON coupons(user_id);
CREATE INDEX idx_coupons_store_id ON coupons(store_id);
CREATE INDEX idx_coupons_campaign_id ON coupons(campaign_id);

-- =============================================
-- 14. PASSWORD_RESET_TOKENS (No changes needed)
-- =============================================
CREATE TABLE password_reset_tokens (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    used_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 15. NEW: REFERRALS TABLE (Critical for marketing)
-- =============================================
CREATE TABLE referrals (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    referrer_id UUID NOT NULL REFERENCES users(id),
    referred_id UUID UNIQUE NOT NULL REFERENCES users(id), -- One referral per new user
    status TEXT NOT NULL CHECK (status IN ('pending', 'completed', 'expired')) DEFAULT 'pending',
    referrer_points_awarded INTEGER DEFAULT 0,
    referred_points_awarded INTEGER DEFAULT 0,
    completed_at TIMESTAMPTZ, -- When referred user made first purchase
    expires_at TIMESTAMPTZ DEFAULT (now() + INTERVAL '30 days'), -- Referral link expires in 30 days
    created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_referrals_referrer_id ON referrals(referrer_id);

-- =============================================
-- 16. NEW: TIER_BENEFITS TABLE (For loyalty program)
-- =============================================
CREATE TABLE tier_benefits (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    tier TEXT NOT NULL CHECK (tier IN ('bronze', 'silver', 'gold')),
    benefit_type TEXT NOT NULL CHECK (benefit_type IN (
        'points_multiplier',
        'free_shipping',
        'early_access',
        'birthday_bonus',
        'exclusive_content'
    )),
    benefit_value JSONB NOT NULL, -- Flexible: could be multiplier rate, boolean, etc.
    description TEXT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 17. NEW: ADMIN_AUDIT_LOG (For compliance)
-- =============================================
CREATE TABLE admin_audit_log (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    admin_id UUID NOT NULL REFERENCES users(id),
    action TEXT NOT NULL,
    entity_type TEXT,
    entity_id UUID,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 18. NEW: MARKETING_SEGMENTS (For targeted campaigns)
-- =============================================
CREATE TABLE marketing_segments (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    segment_name TEXT NOT NULL,
    criteria JSONB NOT NULL, -- JSON criteria for segment rules
    user_count INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    last_calculated_at TIMESTAMPTZ,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now()
);

-- =============================================
-- 19. NEW: MESSAGE_SEQUENCES (For welcome/automated sequences)
-- =============================================
CREATE TABLE message_sequences (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    sequence_name TEXT NOT NULL,
    sequence_type TEXT NOT NULL CHECK (sequence_type IN ('welcome', 'reengagement', 'educational', 'promotional')),
    trigger_event TEXT NOT NULL CHECK (trigger_event IN ('signup', 'first_purchase', 'inactive_30d', 'tier_upgrade', 'manual')),
    store_id UUID REFERENCES stores(id),
    is_active BOOLEAN DEFAULT true,
    total_steps INTEGER DEFAULT 0,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_message_sequences_type ON message_sequences(sequence_type, is_active);

-- =============================================
-- 20. NEW: SEQUENCE_STEPS (Individual messages in sequence)
-- =============================================
CREATE TABLE sequence_steps (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    sequence_id UUID NOT NULL REFERENCES message_sequences(id) ON DELETE CASCADE,
    step_order INTEGER NOT NULL CHECK (step_order > 0),
    delay_days INTEGER NOT NULL DEFAULT 0 CHECK (delay_days >= 0),
    delay_hours INTEGER DEFAULT 0 CHECK (delay_hours >= 0 AND delay_hours < 24),
    title_en TEXT NOT NULL,
    content_en TEXT NOT NULL,
    title_es TEXT,
    content_es TEXT,
    media_url TEXT,
    points_reward INTEGER DEFAULT 0 CHECK (points_reward BETWEEN 0 AND 50),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMPTZ DEFAULT now(),
    UNIQUE(sequence_id, step_order)
);
CREATE INDEX idx_sequence_steps_sequence ON sequence_steps(sequence_id, step_order);

-- =============================================
-- 21. NEW: USER_SEQUENCES (Track user progress through sequences)
-- =============================================
CREATE TABLE user_sequences (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sequence_id UUID NOT NULL REFERENCES message_sequences(id) ON DELETE CASCADE,
    current_step INTEGER DEFAULT 1 CHECK (current_step > 0),
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'completed', 'paused', 'cancelled')),
    started_at TIMESTAMPTZ DEFAULT now(),
    completed_at TIMESTAMPTZ,
    last_step_sent_at TIMESTAMPTZ,
    next_step_due_at TIMESTAMPTZ,
    total_points_earned INTEGER DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT now(),
    UNIQUE(user_id, sequence_id)
);
CREATE INDEX idx_user_sequences_user ON user_sequences(user_id, status);
CREATE INDEX idx_user_sequences_next_due ON user_sequences(next_step_due_at) WHERE status = 'active';

-- =============================================
-- 22. NEW: SCHEDULED_MESSAGES (For recurring weekly/monthly messages)
-- =============================================
CREATE TABLE scheduled_messages (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    message_name TEXT NOT NULL,
    title_en TEXT NOT NULL,
    content_en TEXT NOT NULL,
    title_es TEXT,
    content_es TEXT,
    media_url TEXT,
    schedule_type TEXT NOT NULL CHECK (schedule_type IN ('weekly', 'biweekly', 'monthly')),
    day_of_week INTEGER CHECK (day_of_week BETWEEN 1 AND 7), -- 1=Monday, 7=Sunday
    hour_of_day INTEGER NOT NULL CHECK (hour_of_day BETWEEN 0 AND 23),
    timezone TEXT DEFAULT 'America/New_York',
    target_audience TEXT DEFAULT 'all' CHECK (target_audience IN ('all', 'bronze', 'silver', 'gold', 'segment')),
    segment_criteria JSONB DEFAULT '{}',
    points_reward INTEGER DEFAULT 0 CHECK (points_reward BETWEEN 0 AND 50),
    is_active BOOLEAN DEFAULT true,
    last_sent_at TIMESTAMPTZ,
    next_send_at TIMESTAMPTZ,
    total_sent_count INTEGER DEFAULT 0,
    store_id UUID REFERENCES stores(id),
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_scheduled_messages_next_send ON scheduled_messages(next_send_at) WHERE is_active = true;

-- =============================================
-- INSERT DEFAULT TIER BENEFITS
-- =============================================
INSERT INTO tier_benefits (tier, benefit_type, benefit_value, description) VALUES
-- Bronze Tier (Default)
('bronze', 'points_multiplier', '{"multiplier": 1.0}', 'Standard points earning'),
('bronze', 'birthday_bonus', '{"points": 100}', '100 bonus points on your birthday'),

-- Silver Tier (500+ points)
('silver', 'points_multiplier', '{"multiplier": 1.2}', '20% bonus points on all earnings'),
('silver', 'free_shipping', '{"threshold": 50}', 'Free shipping on orders over $50'),
('silver', 'birthday_bonus', '{"points": 250}', '250 bonus points on your birthday'),

-- Gold Tier (2000+ points)
('gold', 'points_multiplier', '{"multiplier": 1.5}', '50% bonus points on all earnings'),
('gold', 'free_shipping', '{"threshold": 0}', 'Free shipping on all orders'),
('gold', 'early_access', '{"days": 7}', '7-day early access to new products'),
('gold', 'birthday_bonus', '{"points": 500}', '500 bonus points on your birthday'),
('gold', 'exclusive_content', '{"access": true}', 'Access to exclusive wellness content');

-- =============================================
-- CREATE UPDATED_AT TRIGGER FUNCTION
-- =============================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply trigger to tables with updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_user_profiles_updated_at BEFORE UPDATE ON user_profiles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_health_profiles_updated_at BEFORE UPDATE ON health_profiles FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_loyalty_points_updated_at BEFORE UPDATE ON loyalty_points FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_wallet_passes_updated_at BEFORE UPDATE ON wallet_passes FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_qr_campaigns_updated_at BEFORE UPDATE ON qr_campaigns FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =============================================
-- CREATE ROW LEVEL SECURITY (RLS) POLICIES
-- =============================================
-- Enable RLS on all tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE health_profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE loyalty_points ENABLE ROW LEVEL SECURITY;
ALTER TABLE point_transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE wallet_passes ENABLE ROW LEVEL SECURITY;

-- Example RLS Policy for users (adjust based on your needs)
CREATE POLICY "Users can view own profile" ON users FOR SELECT USING (auth.uid() = id);
CREATE POLICY "Users can update own profile" ON users FOR UPDATE USING (auth.uid() = id);

-- =============================================
-- CREATE HELPER FUNCTIONS
-- =============================================
-- Function to calculate tier based on points
CREATE OR REPLACE FUNCTION calculate_user_tier(points_balance INTEGER)
RETURNS TEXT
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    RETURN CASE
        WHEN points_balance >= 2000 THEN 'gold'
        WHEN points_balance >= 500 THEN 'silver'
        ELSE 'bronze'
    END;
END;
$$ LANGUAGE plpgsql;

-- Function to award referral points
CREATE OR REPLACE FUNCTION award_referral_points()
RETURNS TRIGGER
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    -- When a new user signs up with a referral code
    IF NEW.referred_by IS NOT NULL THEN
        -- Award points to referrer
        INSERT INTO point_transactions (user_id, points, transaction_type, description)
        VALUES (
            NEW.referred_by,
            250, -- Referrer bonus
            'referral_bonus',
            'Referral bonus for ' || NEW.email
        );
        
        -- Award points to referred user
        INSERT INTO point_transactions (user_id, points, transaction_type, description)
        VALUES (
            NEW.id,
            250, -- New user bonus
            'referral_signup',
            'Welcome bonus for signing up with referral'
        );
        
        -- Create or update referral record
        INSERT INTO referrals (referrer_id, referred_id, status, referrer_points_awarded, referred_points_awarded, completed_at)
        VALUES (NEW.referred_by, NEW.id, 'completed', 250, 250, now())
        ON CONFLICT (referred_id) DO UPDATE
        SET status = 'completed',
            referrer_points_awarded = 250,
            referred_points_awarded = 250,
            completed_at = now();
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


-- Function to expire old points
CREATE OR REPLACE FUNCTION expire_old_points()
RETURNS void
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    -- Update loyalty_points balance
    UPDATE loyalty_points lp
    SET points_balance = (
        SELECT COALESCE(SUM(points), 0)
        FROM point_transactions pt
        WHERE pt.user_id = lp.user_id
        AND (pt.expires_at IS NULL OR pt.expires_at > NOW())
        AND pt.points > 0
    )
    WHERE EXISTS (
        SELECT 1 FROM point_transactions pt2
        WHERE pt2.user_id = lp.user_id
        AND pt2.expires_at <= NOW()
        AND pt2.expires_at IS NOT NULL
    );
    
    -- Mark expired transactions
    UPDATE point_transactions
    SET transaction_type = 'expiration'
    WHERE expires_at <= NOW()
    AND expires_at IS NOT NULL
    AND transaction_type != 'expiration';
END;
$$ LANGUAGE plpgsql;

-- Function to attribute purchases to campaigns
CREATE OR REPLACE FUNCTION attribute_purchase_to_campaign()
RETURNS TRIGGER
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
    -- When a coupon is marked as used
    IF NEW.is_used = true AND (OLD.is_used IS NULL OR OLD.is_used = false) THEN
        -- Find the campaign that led to this coupon
        IF NEW.campaign_id IS NOT NULL THEN
            -- Update campaign conversion metrics (direct attribution)
            UPDATE qr_campaigns 
            SET conversion_count = conversion_count + 1
            WHERE id = NEW.campaign_id;
        ELSE
            -- Try to attribute via user's signup campaign within attribution window
            UPDATE qr_campaigns qc
            SET conversion_count = conversion_count + 1
            FROM users u
            WHERE u.id = NEW.user_id 
            AND qc.id = u.signup_campaign_id
            AND u.signup_source = 'qr_campaign'
            AND u.created_at >= (NEW.used_at - (NEW.attribution_window_days || ' days')::INTERVAL);
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers
CREATE TRIGGER trigger_referral_award AFTER INSERT ON users FOR EACH ROW EXECUTE FUNCTION award_referral_points();
CREATE TRIGGER trigger_coupon_used AFTER UPDATE ON coupons FOR EACH ROW EXECUTE FUNCTION attribute_purchase_to_campaign();

-- =============================================
-- ENABLE REALTIME REPLICATION
-- =============================================
-- Enable realtime updates for 4 tables (notifications, points, QR scans, loyalty)
-- This allows frontend to subscribe to live database changes

ALTER PUBLICATION supabase_realtime ADD TABLE notifications;
ALTER PUBLICATION supabase_realtime ADD TABLE point_transactions;
ALTER PUBLICATION supabase_realtime ADD TABLE qr_scans;
ALTER PUBLICATION supabase_realtime ADD TABLE loyalty_points;

-- Verify realtime is enabled (optional - just for confirmation)
-- Run this separately to check:
-- SELECT tablename FROM pg_publication_tables WHERE pubname = 'supabase_realtime';
