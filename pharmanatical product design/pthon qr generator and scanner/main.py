# main.py
#
# QR Code Assembler and Scanner (Assembling Version - Final Polish)
#
# This script finds multiple, separate pieces of a QR code from a webcam,
# digitally assembles them, and then scans the result.
#
# NEW: This version ensures no debug lines are included in the assembly and
# adds a final "polishing" step to create a high-contrast B&W image
# before scanning for maximum reliability.
#
# Requirements:
# You only need opencv-python and numpy.
#
# How to Run:
# 1. Save this code as main.py.
# 2. Open a command prompt in the correct folder.
# 3. Run the script using: py main.py
# 4. Hold up the 4 printed/displayed QR code parts to your webcam.
# 5. The script will assemble the pieces in the "Assembled QR Code" window.
# 6. If successful, the decoded data will be printed on the main camera feed.
# 7. Press 'q' to quit the application.

import cv2
import numpy as np

def order_points(pts):
    """
    Sorts the four corner points of a contour in top-left, top-right,
    bottom-right, bottom-left order. This is crucial for the perspective transform.
    """
    rect = np.zeros((4, 2), dtype="float32")
    
    s = pts.sum(axis=1)
    rect[0] = pts[np.argmin(s)]
    rect[2] = pts[np.argmax(s)]
    
    diff = np.diff(pts, axis=1)
    rect[1] = pts[np.argmin(diff)]
    rect[3] = pts[np.argmax(diff)]
    
    return rect

def find_and_assemble_qr_pieces(frame, qr_detector):
    """
    Finds potential QR code pieces, corrects their perspective, assembles
    them into a perfect grid, and then decodes.
    """
    # Create a copy of the frame for drawing, leaving the original clean
    frame_with_contours = frame.copy()
    
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    thresh = cv2.adaptiveThreshold(blurred, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                   cv2.THRESH_BINARY_INV, 11, 2)

    contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    qr_pieces_data = []
    for c in contours:
        area = cv2.contourArea(c)
        if area < 500 or area > 70000:
            continue

        peri = cv2.arcLength(c, True)
        approx = cv2.approxPolyDP(c, 0.04 * peri, True)

        if len(approx) == 4 and cv2.isContourConvex(approx):
            M = cv2.moments(approx)
            if M["m00"] == 0: continue
            cX = int(M["m10"] / M["m00"])
            cY = int(M["m01"] / M["m00"])
            
            qr_pieces_data.append({'contour': approx, 'cx': cX, 'cy': cY})
            # Draw contours on the copy, not the original frame
            cv2.drawContours(frame_with_contours, [approx], -1, (0, 255, 0), 2)

    decoded_data = None
    if len(qr_pieces_data) == 4:
        qr_pieces_data.sort(key=lambda p: p['cy'])
        top_row = sorted(qr_pieces_data[0:2], key=lambda p: p['cx'])
        bottom_row = sorted(qr_pieces_data[2:4], key=lambda p: p['cx'])
        
        sorted_pieces = [top_row[0], top_row[1], bottom_row[0], bottom_row[1]]

        piece_size = 150
        canvas_size = piece_size * 2
        # Assembled canvas starts as a color image
        assembled_color_canvas = np.zeros((canvas_size, canvas_size, 3), dtype=np.uint8)
        
        destinations = {
            0: (0, 0), 1: (piece_size, 0),
            2: (0, piece_size), 3: (piece_size, piece_size)
        }

        for i, piece_data in enumerate(sorted_pieces):
            contour = piece_data['contour']
            src_points = order_points(contour.reshape(4, 2))
            
            dst_points = np.array([
                [0, 0], [piece_size - 1, 0],
                [piece_size - 1, piece_size - 1], [0, piece_size - 1]
            ], dtype="float32")

            # IMPORTANT: Use the original, clean 'frame' for warping, not the copy with contours
            matrix = cv2.getPerspectiveTransform(src_points, dst_points)
            warped_piece = cv2.warpPerspective(frame, matrix, (piece_size, piece_size))
            
            x_offset, y_offset = destinations[i]
            assembled_color_canvas[y_offset:y_offset + piece_size, x_offset:x_offset + piece_size] = warped_piece

        # --- Final Polish Step ---
        # Convert the assembled color canvas to grayscale
        final_gray = cv2.cvtColor(assembled_color_canvas, cv2.COLOR_BGR2GRAY)
        # Apply a sharp threshold to get a pure Black & White image
        _, final_bw = cv2.threshold(final_gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)

        # Display the final, polished B&W image that will be scanned
        cv2.imshow("Assembled & Polished QR Code", final_bw)

        # --- Decode the perfectly polished canvas ---
        try:
            # Use the final_bw image for decoding
            decoded_text, _, _ = qr_detector.detectAndDecode(final_bw)
            if decoded_text:
                decoded_data = decoded_text
                cv2.putText(frame_with_contours, "DECODED:", (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 
                            0.8, (0, 0, 255), 2)
                cv2.putText(frame_with_contours, decoded_data, (10, 60), cv2.FONT_HERSHEY_SIMPLEX, 
                            0.7, (0, 0, 255), 2)
        except Exception as e:
            print(f"[WARN] QRCodeDetector error: {e}")

    # Return the frame with contours for display
    return frame_with_contours, decoded_data

def main():
    qr_detector = cv2.QRCodeDetector()
    print("[INFO] Starting video stream...")
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("[ERROR] Cannot open camera. Please check if it is connected.")
        return

    while True:
        ret, frame = cap.read()
        if not ret:
            break

        frame = cv2.flip(frame, 1)
        processed_frame, decoded_text = find_and_assemble_qr_pieces(frame, qr_detector)

        if decoded_text:
            print(f"[SUCCESS] Decoded Data: {decoded_text}")

        cv2.imshow("QR Code Assembler - Press 'q' to quit", processed_frame)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    print("[INFO] Stopping video stream and closing windows.")
    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
