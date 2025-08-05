# Blockchain Implementation in MedChain

## What is Blockchain?

Blockchain is a digital ledger technology that records transactions in a secure, transparent, and tamper-proof way. In MedChain, it works like this:

1. **Chain of Blocks**: Each transaction is a "block" linked to the one before it
2. **Immutable Records**: Once data is added, it cannot be changed without detection
3. **Distributed**: The ledger is maintained across multiple systems for security

## Visual Representation

```
Block 1 (Genesis) → Block 2 → Block 3 → ... → Block N
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Product: ABC123 │    │ Dispensed to    │    │ Sold to         │
│ Action: Created │    │ Distributor X   │    │ Pharmacist Y    │
│ Hash: a1b2c3... │    │ Hash: d4e5f6... │    │ Hash: g7h8i9... │
│ Prev: 0000...   │    │ Prev: a1b2c3... │    │ Prev: d4e5f6... │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## How It Works in the Background

1. **When an Action Occurs** (e.g., product dispensed):
   - System creates a new block with action details
   - Block includes timestamp and actor information
   - A unique hash is generated for the block
   - The hash includes the previous block's hash

2. **Chain Verification** (`verify_chain.php`):
   - Validates each block's hash
   - Ensures previous hash references are correct
   - Detects any tampering attempts

3. **In Your Database**:
   - `audit_trail` table stores all blocks
   - Each row contains block data and hashes
   - System can verify the entire chain's integrity

## Core Security Features

### 1. Immutable Ledger
- Each transaction is recorded as a block in the blockchain
- Blocks contain cryptographic hashes linking them together
- Tampering with any block breaks the chain, making alterations easily detectable

### 2. Data Integrity
- SHA-256 hashing ensures data cannot be modified without detection
- Each block includes:
  - Action details (what changed)
  - Actor information (who made the change)
  - Timestamp (when it happened)
  - Reference to previous block

## How It Works

### 1. Product Lifecycle
```
Manufacturer → Distributor → Pharmacist → Patient
```
- Each handoff is recorded on the blockchain
- Complete audit trail from creation to consumption

### 2. Verification Process
- System periodically verifies chain integrity
- Checks:
  - Block hashes are valid
  - Previous hash references are correct
  - No blocks have been tampered with

### 3. Security Measures
- Role-based access control
- All actions are logged and immutable
- Real-time verification of product authenticity

## Technical Implementation

### Database Structure
- `audit_trail` table stores all blockchain transactions
- Each record includes:
  - `current_hash`: Hash of current block data
  - `previous_hash`: Hash of previous block
  - Action details in JSON format

### Verification Endpoint
- `verify_chain.php` validates the entire blockchain
- Returns verification status for each block
- Can detect even minor data tampering

## Implementation in MedChain

### Visual Flow
```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│ 1. Product      │     │ 2. Add to       │     │ 3. Verify       │
│    Created      │     │    Blockchain   │     │    Chain        │
│    (register_   │────▶│    (audit_     │────▶│    (verify_     │
│    product.php) │     │    trail table) │     │    chain.php)   │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │                        │
        ▼                       ▼                        ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│ 4. View         │     │ 5. Track        │     │ 6. Detect       │
│    History      │     │    Product      │     │    Tampering    │
│    (history.php)│     │    (trace_      │     │    (automatic   │
│                 │     │    product.php) │     │    verification)│
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

### Key Implementation Files

1. **Database Layer**
   - `medchain_db.sql`: Defines the `audit_trail` table structure
   - `api/db_connect.php`: Database connection handler

2. **Blockchain Operations**
   - `api/register_product.php`: Creates genesis block for new products
   - `api/dispense_product.php`: Adds blocks for product movements
   - `api/verify_chain.php`: Validates blockchain integrity
   - `api/trace_product.php`: Retrieves product history

3. **Frontend**
   - `admin/audit.php`: Displays blockchain audit trail
   - `manufacturer/history.php`: Shows product history
   - `distributor/dashboard.php`: Tracks product movements

4. **Security**
   - `config.php`: Contains hash algorithm configuration
   - `api/verify_chain.php`: Runs periodic integrity checks

## Benefits
1. **Transparency**: Complete history of every product
2. **Security**: Cryptographic protection against tampering
3. **Accountability**: Every action is tied to a specific user
4. **Traceability**: Full audit trail from manufacturer to patient
