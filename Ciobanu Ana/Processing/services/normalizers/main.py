from Processing.services.normalizers.date_normalizer import DateNormalizer

if __name__ == "__main__":
    date_normalizer = DateNormalizer()

    # Call normalize with a date string
    try:
        normalized_date = date_normalizer.normalize("202411-10")
        print(normalized_date)  # Expected output: '10/11/2024'
    except ValueError as e:
        print(f"Error: {e}")
