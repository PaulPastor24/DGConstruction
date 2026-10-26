-- Real construction material types; ALL quantities and transactions are fictional demo data.
-- Not a bill of quantities, structural design, procurement recommendation, or actual delivery evidence.
-- Run AFTER update_demo_project_variations_2023_2028.sql. Back up first.
-- Preserves existing accounts and operational data. Creates separate marked demo material records.
-- Planning projects receive no materials. Allocations go to started/completed demo phases only.
-- Warehouse received = project allocations + warehouse ending stock.
-- Project remaining = allocation - usage. Usage does not deduct warehouse stock again.
-- No prices, real supplier claims, or photographs are fabricated.
-- Catalog: 120 distinct construction materials. Safe to rerun; only missing names are added.
-- Existing stock, allocations, receipts and usage are not overwritten.
-- Import the ENTIRE file in phpMyAdmin. Requires current migrated MySQL/MariaDB schema.
DROP PROCEDURE IF EXISTS dg_add_demo_materials_2023_2028;
DELIMITER $$
CREATE PROCEDURE dg_add_demo_materials_2023_2028()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    IF NOT EXISTS (SELECT 1 FROM projects WHERE description LIKE 'Demonstration project.%') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insert the demonstration projects and phases first.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM project_supervisors ps JOIN usrs u ON u.user_id=ps.supervisor_id WHERE u.is_active=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An active assigned supervisor is required for usage records.';
    END IF;
    DROP TEMPORARY TABLE IF EXISTS demo_material_catalog;
    CREATE TEMPORARY TABLE demo_material_catalog (
        material_name VARCHAR(255), category_name VARCHAR(100), unit_name VARCHAR(30),
        phase_order_num INT, base_allocation DECIMAL(12,2), minimum_qty DECIMAL(12,2), ending_qty DECIMAL(12,2)
    );
    INSERT INTO demo_material_catalog VALUES
    ('Portland Cement - 40 kg bag','Cement','bags',2,240,60,500),
    ('Washed Sand','Aggregates','m3',2,18,8,60),
    ('Crushed Gravel - 19 mm','Aggregates','m3',2,24,10,80),
    ('Deformed Reinforcing Bar - 12 mm x 6 m','Steel','pieces',2,160,50,300),
    ('Deformed Reinforcing Bar - 16 mm x 6 m','Steel','pieces',3,180,50,260),
    ('Annealed Tie Wire - No. 16','Steel','kg',3,24,10,35),
    ('Phenolic Formwork Plywood - 12 mm x 1.22 m x 2.44 m','Lumber and Formwork','sheets',3,40,10,20),
    ('Common Wire Nails - 3 inch','Hardware','kg',3,15,5,0),
    ('Concrete Hollow Block - 150 mm','Masonry','pieces',4,900,200,1200),
    ('uPVC Sanitary Pipe - 100 mm x 3 m','Plumbing','lengths',4,20,8,45),
    ('PPR Water Pipe - 20 mm x 4 m','Plumbing','lengths',4,30,10,60),
    ('THHN Copper Wire - 3.5 mm2','Electrical','m',4,450,100,800),
    ('PVC Electrical Conduit - 20 mm x 3 m','Electrical','lengths',4,80,20,120),
    ('Prepainted Long Span Roofing Sheet - 0.50 mm x 3 m','Roofing','sheets',4,60,15,75),
    ('Ceramic Floor Tile - 600 mm x 600 mm','Finishing','pieces',4,450,100,90),
    ('Water Based Interior Latex Paint - 16 L','Painting','pails',4,12,5,3),
    ('Deformed Reinforcing Bar - 10 mm x 6 m','Steel','pieces',3,100,20,150),
    ('Deformed Reinforcing Bar - 20 mm x 6 m','Steel','pieces',3,100,20,150),
    ('Deformed Reinforcing Bar - 25 mm x 6 m','Steel','pieces',3,100,20,150),
    ('Deformed Reinforcing Bar - 28 mm x 6 m','Steel','pieces',3,100,20,150),
    ('Deformed Reinforcing Bar - 32 mm x 6 m','Steel','pieces',3,100,20,150),
    ('uPVC 90 Degree Elbow - 50 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC 45 Degree Elbow - 50 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Equal Tee - 50 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Coupling - 50 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Cleanout with Plug - 50 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC 90 Degree Elbow - 75 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC 45 Degree Elbow - 75 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Equal Tee - 75 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Coupling - 75 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Cleanout with Plug - 75 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC 90 Degree Elbow - 100 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC 45 Degree Elbow - 100 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Equal Tee - 100 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Coupling - 100 mm','Plumbing','pieces',4,20,4,30),
    ('uPVC Cleanout with Plug - 100 mm','Plumbing','pieces',4,20,4,30),
    ('PPR 90 Degree Elbow - 20 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Equal Tee - 20 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Coupling - 20 mm','Plumbing','pieces',4,30,6,45),
    ('PPR End Cap - 20 mm','Plumbing','pieces',4,30,6,45),
    ('PPR 90 Degree Elbow - 25 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Equal Tee - 25 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Coupling - 25 mm','Plumbing','pieces',4,30,6,45),
    ('PPR End Cap - 25 mm','Plumbing','pieces',4,30,6,45),
    ('PPR 90 Degree Elbow - 32 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Equal Tee - 32 mm','Plumbing','pieces',4,30,6,45),
    ('PPR Coupling - 32 mm','Plumbing','pieces',4,30,6,45),
    ('PPR End Cap - 32 mm','Plumbing','pieces',4,30,6,45),
    ('THHN Copper Wire - 2 mm2','Electrical','m',4,200,40,300),
    ('THHN Copper Wire - 5.5 mm2','Electrical','m',4,200,40,300),
    ('THHN Copper Wire - 8 mm2','Electrical','m',4,200,40,300),
    ('THHN Copper Wire - 14 mm2','Electrical','m',4,200,40,300),
    ('THHN Copper Wire - 22 mm2','Electrical','m',4,200,40,300),
    ('PVC Conduit Coupling - 20 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Elbow - 20 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Male Adapter - 20 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Coupling - 25 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Elbow - 25 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Male Adapter - 25 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Coupling - 32 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Elbow - 32 mm','Electrical','pieces',4,40,8,60),
    ('PVC Conduit Male Adapter - 32 mm','Electrical','pieces',4,40,8,60),
    ('Circuit Breaker - 1 Pole, 15 A','Electrical','pieces',4,8,2,12),
    ('Circuit Breaker - 1 Pole, 20 A','Electrical','pieces',4,8,2,12),
    ('Circuit Breaker - 1 Pole, 30 A','Electrical','pieces',4,8,2,12),
    ('Circuit Breaker - 1 Pole, 40 A','Electrical','pieces',4,8,2,12),
    ('Circuit Breaker - 2 Pole, 30 A','Electrical','pieces',4,4,1,6),
    ('Circuit Breaker - 2 Pole, 40 A','Electrical','pieces',4,4,1,6),
    ('Circuit Breaker - 2 Pole, 50 A','Electrical','pieces',4,4,1,6),
    ('Circuit Breaker - 2 Pole, 60 A','Electrical','pieces',4,4,1,6),
    ('Common Wire Nails - 2 inch','Hardware','kg',3,12,3,18),
    ('Common Wire Nails - 4 inch','Hardware','kg',3,12,3,18),
    ('Expansion Bolt - M8 x 100 mm','Hardware','sets',3,50,10,75),
    ('Expansion Bolt - M10 x 100 mm','Hardware','sets',3,50,10,75),
    ('Expansion Bolt - M12 x 100 mm','Hardware','sets',3,50,10,75),
    ('Lumber - 2 x 2 inch x 10 ft','Lumber and Formwork','pieces',3,50,10,75),
    ('Lumber - 2 x 3 inch x 10 ft','Lumber and Formwork','pieces',3,50,10,75),
    ('Lumber - 2 x 4 inch x 10 ft','Lumber and Formwork','pieces',3,50,10,75),
    ('Lumber - 2 x 6 inch x 10 ft','Lumber and Formwork','pieces',3,50,10,75),
    ('Marine Plywood - 18 mm x 1.22 x 2.44 m','Lumber and Formwork','sheets',3,30,6,45),
    ('Ordinary Plywood - 6 mm x 1.22 x 2.44 m','Lumber and Formwork','sheets',3,30,6,45),
    ('Fine Plastering Sand','Aggregates','m3',2,20,4,30),
    ('Base Course Aggregate','Aggregates','m3',2,20,4,30),
    ('Selected Fill Material','Aggregates','m3',2,20,4,30),
    ('Crushed Gravel - 10 mm','Aggregates','m3',2,20,4,30),
    ('Concrete Hollow Block - 100 mm','Masonry','pieces',4,400,80,600),
    ('AAC Block - 100 x 200 x 600 mm','Masonry','pieces',4,400,80,600),
    ('Masonry Cement - 40 kg','Cement and Mortar','bags',4,40,8,60),
    ('Tile Adhesive - 25 kg','Cement and Mortar','bags',4,40,8,60),
    ('Non Shrink Grout - 25 kg','Cement and Mortar','bags',4,40,8,60),
    ('Hydrated Lime - 25 kg','Cement and Mortar','bags',4,40,8,60),
    ('Prepainted Ridge Roll - 0.50 mm x 2.4 m','Roofing','pieces',4,15,3,23),
    ('Prepainted Flashing - 0.50 mm x 2.4 m','Roofing','pieces',4,15,3,23),
    ('Prepainted Gutter - 0.50 mm x 2.4 m','Roofing','pieces',4,15,3,23),
    ('uPVC Sanitary Pipe - 50 mm x 3 m','Plumbing','lengths',4,20,4,30),
    ('uPVC Sanitary Pipe - 75 mm x 3 m','Plumbing','lengths',4,20,4,30),
    ('PPR Water Pipe - 25 mm x 4 m','Plumbing','lengths',4,20,4,30),
    ('PPR Water Pipe - 32 mm x 4 m','Plumbing','lengths',4,20,4,30),
    ('Stainless Steel Single Bowl Kitchen Sink','Plumbing','sets',4,6,2,9),
    ('Two Piece Floor Mounted Water Closet','Plumbing','sets',4,6,2,9),
    ('Ceramic Lavatory with Faucet','Plumbing','sets',4,6,2,9),
    ('Shower Mixer and Shower Head','Plumbing','sets',4,6,2,9),
    ('Grounded Duplex Outlet - 16 A','Electrical','pieces',4,30,6,45),
    ('Single Gang Light Switch - 10 A','Electrical','pieces',4,30,6,45),
    ('LED Downlight - 12 W','Electrical','pieces',4,30,6,45),
    ('LED Batten Light - 20 W, 1.2 m','Electrical','pieces',4,30,6,45),
    ('PVC Utility Box - 50 x 100 mm','Electrical','pieces',4,30,6,45),
    ('PVC Junction Box - 100 x 100 mm','Electrical','pieces',4,30,6,45),
    ('Gypsum Board - 12 mm x 1.22 x 2.44 m','Finishing','sheets',4,35,7,53),
    ('Fiber Cement Board - 4.5 mm x 1.22 x 2.44 m','Finishing','sheets',4,35,7,53),
    ('Porcelain Floor Tile - 600 x 600 mm','Finishing','pieces',4,200,40,300),
    ('Ceramic Wall Tile - 300 x 600 mm','Finishing','pieces',4,200,40,300),
    ('Concrete Primer - 16 L','Painting','pails',4,10,2,15),
    ('Exterior Acrylic Paint - 16 L','Painting','pails',4,10,2,15),
    ('Enamel Paint - 4 L','Painting','cans',4,8,2,12),
    ('Red Oxide Metal Primer - 4 L','Painting','cans',4,8,2,12),
    ('Paint Thinner - 4 L','Painting','cans',4,8,2,12),
    ('Neutral Cure Silicone Sealant - 300 mL','Finishing','cartridges',4,20,4,30),
    ('Acrylic Latex Sealant - 300 mL','Finishing','cartridges',4,20,4,30),
    ('Cementitious Waterproofing - 20 kg kit','Waterproofing','kits',4,12,3,18);
    START TRANSACTION;
    -- Skip existing material names, including those from the original 16-item import.
    DELETE c FROM demo_material_catalog c JOIN materials m ON m.name=c.material_name;
    INSERT INTO materials (name,category,unit,current_stock,minimum_stock_level,supplier,description,created_at,updated_at)
    SELECT material_name,category_name,unit_name,ending_qty,minimum_qty,'Demonstration supplier (fictional)',
        CONCAT('[DEMO-MATERIALS-2023-2028] ',material_name,'. Fictional quantities for demonstration; verify actual specifications and quantities against project drawings.'),NOW(),NOW()
    FROM demo_material_catalog;

    DROP TEMPORARY TABLE IF EXISTS demo_material_allocations;
    CREATE TEMPORARY TABLE demo_material_allocations AS
    SELECT p.project_id,ph.phase_id,m.id AS material_id,m.unit,
        CASE WHEN c.unit_name IN ('bags','pieces','sheets','lengths','pails','sets','rolls','kits','cans','cartridges') THEN CEIL(c.base_allocation * (1 + MOD(CHAR_LENGTH(p.project_name),4)*0.25)) ELSE ROUND(c.base_allocation * (1 + MOD(CHAR_LENGTH(p.project_name),4)*0.25),2) END AS allocation_qty,
        ph.completion_percentage AS phase_progress,
        COALESCE(ph.actual_start_date,ph.planned_start_date) AS allocation_date,
        CASE WHEN ph.status='completed' THEN COALESCE(ph.actual_end_date,ph.planned_end_date) ELSE DATE('2026-10-26') END AS usage_date,
        assignment.supervisor_id
    FROM projects p JOIN construction_phases ph ON ph.project_id=p.project_id
    JOIN demo_material_catalog c ON c.phase_order_num=ph.phase_order
    JOIN materials m ON m.name=c.material_name AND m.description LIKE '[DEMO-MATERIALS-2023-2028]%'
    JOIN (SELECT ps.project_id,MIN(ps.supervisor_id) supervisor_id FROM project_supervisors ps JOIN users u ON u.user_id=ps.supervisor_id WHERE ps.is_active=1 AND u.is_active=1 GROUP BY ps.project_id) assignment ON assignment.project_id=p.project_id
    WHERE p.description LIKE 'Demonstration project.%' AND p.status IN ('ongoing','completed')
      AND ph.override_reason='Demonstration baseline progress' AND ph.completion_percentage>0;

    INSERT INTO project_materials (project_id,material_id,planned_quantity,used_quantity,unit,created_at,updated_at)
    SELECT project_id,material_id,allocation_qty,
        CASE WHEN unit IN ('bags','pieces','sheets','lengths','pails','sets','rolls','kits','cans','cartridges') THEN FLOOR(allocation_qty*phase_progress/100) ELSE ROUND(allocation_qty*phase_progress/100,2) END,
        unit,TIMESTAMP(allocation_date,'08:00:00'),NOW()
    FROM demo_material_allocations;

    -- Warehouse receipt includes everything allocated plus stock still in storage.
    INSERT INTO material_deliveries (material_id,project_id,quantity,unit,total_price,supplier_name,delivered_at,notes,created_at,updated_at)
    SELECT m.id,NULL,m.current_stock+COALESCE(SUM(a.allocation_qty),0),m.unit,NULL,
        'Demonstration supplier (fictional)',COALESCE(MIN(a.allocation_date),DATE('2026-10-26')),
        '[DEMO-MATERIALS-2023-2028] Warehouse receipt; fictional demonstration transaction.',NOW(),NOW()
    FROM materials m LEFT JOIN demo_material_allocations a ON a.material_id=m.id
    WHERE m.description LIKE '[DEMO-MATERIALS-2023-2028]%'
      AND EXISTS (SELECT 1 FROM demo_material_catalog c WHERE c.material_name=m.name)
    GROUP BY m.id,m.current_stock,m.unit;

    INSERT INTO material_deliveries (material_id,project_id,quantity,unit,total_price,supplier_name,delivered_at,notes,created_at,updated_at)
    SELECT material_id,project_id,allocation_qty,unit,NULL,'Internal Warehouse - Demo',allocation_date,
        '[DEMO-MATERIALS-2023-2028] Transfer from warehouse to project allocation; not an additional purchase.',TIMESTAMP(allocation_date,'08:00:00'),NOW()
    FROM demo_material_allocations;

    INSERT INTO material_usages (project_id,phase_id,material_id,quantity_used,unit,usage_date,remarks,recorded_by,site_photo_path,created_at,updated_at)
    SELECT a.project_id,a.phase_id,a.material_id,pm.used_quantity,a.unit,a.usage_date,
        '[DEMO-MATERIALS-2023-2028] Sample consumption for the linked construction phase; no actual delivery or inspection claim.',a.supervisor_id,NULL,TIMESTAMP(a.usage_date,'15:00:00'),NOW()
    FROM demo_material_allocations a JOIN project_materials pm ON pm.project_id=a.project_id AND pm.material_id=a.material_id
    WHERE pm.used_quantity>0;
    COMMIT;
    DROP TEMPORARY TABLE demo_material_allocations;
    DROP TEMPORARY TABLE demo_material_catalog;
END$$
DELIMITER ;
CALL dg_add_demo_materials_2023_2028();
DROP PROCEDURE dg_add_demo_materials_2023_2028;

-- Verify project balances.
SELECT p.project_name,m.name,pm.unit,pm.planned_quantity AS allocation,pm.used_quantity AS used,
    pm.planned_quantity-pm.used_quantity AS project_remaining
FROM project_materials pm JOIN projects p ON p.project_id=pm.project_id JOIN materials m ON m.id=pm.material_id
WHERE m.description LIKE '[DEMO-MATERIALS-2023-2028]%' ORDER BY p.project_name,m.name;

-- Every reconciliation_difference should be zero.
SELECT m.name,m.current_stock AS warehouse_remaining,
    COALESCE(receipts.qty,0) AS warehouse_received,COALESCE(allocations.qty,0) AS allocated_to_projects,
    COALESCE(receipts.qty,0)-COALESCE(allocations.qty,0)-m.current_stock AS reconciliation_difference
FROM materials m
LEFT JOIN (SELECT material_id,SUM(quantity) qty FROM material_deliveries WHERE project_id IS NULL GROUP BY material_id) receipts ON receipts.material_id=m.id
LEFT JOIN (SELECT material_id,SUM(planned_quantity) qty FROM project_materials GROUP BY material_id) allocations ON allocations.material_id=m.id
WHERE m.description LIKE '[DEMO-MATERIALS-2023-2028]%';
