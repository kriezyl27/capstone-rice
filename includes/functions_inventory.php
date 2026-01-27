<?php
// ../includes/functions_inventory.php

function inventoryTransaction(
    $conn,
    $product_id,
    $qty_kg,
    $type,
    $reference_type,
    $reference_id = null,
    $note = null
) {
    $sql = "INSERT INTO inventory_transactions
            (product_id, qty_kg, type, reference_type, reference_id, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "idssis",
        $product_id,
        $qty_kg,
        $type,
        $reference_type,
        $reference_id,
        $note
    );

    return $stmt->execute();
}

/* ===== Helpers ===== */

function inventoryIn($conn, $product_id, $qty_kg, $ref_type, $ref_id, $note) {
    return inventoryTransaction(
        $conn,
        $product_id,
        $qty_kg,
        'in',
        $ref_type,
        $ref_id,
        $note
    );
}

function inventoryOut($conn, $product_id, $qty_kg, $ref_type, $ref_id, $note) {
    return inventoryTransaction(
        $conn,
        $product_id,
        $qty_kg,
        'out',
        $ref_type,
        $ref_id,
        $note
    );
}

function inventoryAdjust($conn, $product_id, $qty_kg, $note) {
    return inventoryTransaction(
        $conn,
        $product_id,
        $qty_kg,
        'adjust',
        'adjust',
        null,
        $note
    );
}
