vi.mock('axios');

import { describe, it, expect, vi, beforeEach } from 'vitest';
import axios from 'axios';
import { 
    fetchProducts, 
    createProduct, 
    updateProduct, 
    deleteProduct, 
    fetchProduct,
    attachSupplier,
    updateProductSupplier,
    detachSupplier
} from '@/services/productService';

const mockedAxios = vi.mocked(axios);

describe('productService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('fetchProducts', () => {
        it('should fetch products successfully', async () => {
            // Arrange
            const mockResponse = {
                data: {
                    data: {
                        products: {
                            data: [{ id: '01HX123', name: 'Product 1', category: 'engine' }],
                            total: 1,
                            current_page: 1,
                            per_page: 10,
                        }
                    }
                }
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            // Act
            const result = await fetchProducts({ page: 1, perPage: 10 });

            // Assert
            expect(mockedAxios.get).toHaveBeenCalledWith('/products/search', {
                params: {
                    page: 1,
                    per_page: 10,
                    search: '',
                }
            });
            expect(result).toEqual({
                items: [{ id: '01HX123', name: 'Product 1', category: 'engine' }],
                total: 1,
                page: 1,
                perPage: 10,
            });
        });

        it('should handle fetch error', async () => {
            // Arrange
            mockedAxios.get.mockRejectedValue(new Error('Network error'));

            // Act & Assert
            await expect(fetchProducts()).rejects.toThrow('Network error');
        });
    });

    describe('createProduct', () => {
        it('should create product successfully', async () => {
            // Arrange
            const productData = { name: 'New Product', category: 'engine', unit: 'unit', unit_price: 100 };
            const mockResponse = { data: { id: '01HX456', ...productData } };
            mockedAxios.post.mockResolvedValue(mockResponse);

            // Act
            const result = await createProduct(productData);

            // Assert
            expect(mockedAxios.post).toHaveBeenCalledWith('/products', productData);
            expect(result).toEqual({ success: true, data: { id: '01HX456', ...productData } });
        });

        it('should handle create error', async () => {
            // Arrange
            const productData = { name: 'New Product' };
            const error = new Error('Validation error');
            mockedAxios.post.mockRejectedValue(error);

            // Act
            const result = await createProduct(productData);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('updateProduct', () => {
        it('should update product successfully', async () => {
            // Arrange
            const id = '01HX123';
            const productData = { name: 'Updated Product' };
            const mockResponse = { data: { id, ...productData } };
            mockedAxios.put.mockResolvedValue(mockResponse);

            // Act
            const result = await updateProduct(id, productData);

            // Assert
            expect(mockedAxios.put).toHaveBeenCalledWith('/products/01HX123', productData);
            expect(result).toEqual({ success: true, data: { id, ...productData } });
        });

        it('should handle update error', async () => {
            // Arrange
            const id = '01HX123';
            const productData = { name: 'Updated Product' };
            const error = new Error('Update error');
            mockedAxios.put.mockRejectedValue(error);

            // Act
            const result = await updateProduct(id, productData);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('deleteProduct', () => {
        it('should delete product successfully', async () => {
            // Arrange
            const id = '01HX123';
            const mockResponse = { data: {} };
            mockedAxios.delete.mockResolvedValue(mockResponse);

            // Act
            const result = await deleteProduct(id);

            // Assert
            expect(mockedAxios.delete).toHaveBeenCalledWith('/products/01HX123');
            expect(result).toEqual({ success: true, data: {} });
        });

        it('should handle delete error', async () => {
            // Arrange
            const id = '01HX123';
            const error = new Error('Delete error');
            mockedAxios.delete.mockRejectedValue(error);

            // Act
            const result = await deleteProduct(id);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('fetchProduct', () => {
        it('should fetch single product successfully', async () => {
            // Arrange
            const id = '01HX123';
            const mockResponse = { 
                data: { 
                    data: { 
                        product: { id: '01HX123', name: 'Product 1', suppliers: [] } 
                    } 
                } 
            };
            mockedAxios.get.mockResolvedValue(mockResponse);

            // Act
            const result = await fetchProduct(id);

            // Assert
            expect(mockedAxios.get).toHaveBeenCalledWith('/products/01HX123');
            expect(result).toEqual({ 
                success: true, 
                data: { 
                    data: { 
                        product: { id: '01HX123', name: 'Product 1', suppliers: [] } 
                    } 
                } 
            });
        });

        it('should handle fetch product error', async () => {
            // Arrange
            const id = '01HX123';
            const error = new Error('Not found');
            mockedAxios.get.mockRejectedValue(error);

            // Act
            const result = await fetchProduct(id);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('attachSupplier', () => {
        it('should attach supplier to product successfully', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierData = { supplier_id: '01HX456', unit_price: 50, delivery_time: 7 };
            const mockResponse = { 
                data: { 
                    data: { 
                        product: { id: productId, suppliers: [{ id: '01HX456' }] } 
                    } 
                } 
            };
            mockedAxios.post.mockResolvedValue(mockResponse);

            // Act
            const result = await attachSupplier(productId, supplierData);

            // Assert
            expect(mockedAxios.post).toHaveBeenCalledWith('/products/01HX123/suppliers', supplierData);
            expect(result).toEqual({ success: true, data: mockResponse.data });
        });

        it('should handle attach supplier error', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierData = { supplier_id: '01HX456' };
            const error = new Error('Supplier already attached');
            mockedAxios.post.mockRejectedValue(error);

            // Act
            const result = await attachSupplier(productId, supplierData);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('updateProductSupplier', () => {
        it('should update product supplier successfully', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierId = '01HX456';
            const supplierData = { unit_price: 60, delivery_time: 5 };
            const mockResponse = { 
                data: { 
                    data: { 
                        product: { id: productId } 
                    } 
                } 
            };
            mockedAxios.put.mockResolvedValue(mockResponse);

            // Act
            const result = await updateProductSupplier(productId, supplierId, supplierData);

            // Assert
            expect(mockedAxios.put).toHaveBeenCalledWith('/products/01HX123/suppliers/01HX456', supplierData);
            expect(result).toEqual({ success: true, data: mockResponse.data });
        });

        it('should handle update product supplier error', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierId = '01HX456';
            const supplierData = { unit_price: 60 };
            const error = new Error('Supplier not found');
            mockedAxios.put.mockRejectedValue(error);

            // Act
            const result = await updateProductSupplier(productId, supplierId, supplierData);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });

    describe('detachSupplier', () => {
        it('should detach supplier from product successfully', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierId = '01HX456';
            const mockResponse = { data: {} };
            mockedAxios.delete.mockResolvedValue(mockResponse);

            // Act
            const result = await detachSupplier(productId, supplierId);

            // Assert
            expect(mockedAxios.delete).toHaveBeenCalledWith('/products/01HX123/suppliers/01HX456');
            expect(result).toEqual({ success: true, data: {} });
        });

        it('should handle detach supplier error', async () => {
            // Arrange
            const productId = '01HX123';
            const supplierId = '01HX456';
            const error = new Error('Supplier not attached');
            mockedAxios.delete.mockRejectedValue(error);

            // Act
            const result = await detachSupplier(productId, supplierId);

            // Assert
            expect(result).toEqual({ success: false, error });
        });
    });
});
