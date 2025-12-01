// js/shop.js - Versão simplificada e robusta
class Shop {
    static async apiCall(endpoint, data = null) {
        try {
            const url = `api.php?action=${endpoint}`;
            const options = {
                method: data ? 'POST' : 'GET',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            };

            if (data) {
                const params = new URLSearchParams();
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        params.append(key, data[key]);
                    }
                }
                options.body = params;
            }

            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Erro na API:', error);
            throw new Error('Falha na comunicação com o servidor');
        }
    }

    static async getProducts() {
        return await this.apiCall('products');
    }

    static async getCart() {
        try {
            return await this.apiCall('cart');
        } catch (error) {
            console.error('Erro ao obter carrinho:', error);
            return { items: [], total: 0 };
        }
    }

    static async addToCart(productId, quantity = 1) {
        return await this.apiCall('addToCart', {
            id: productId,
            qty: quantity
        });
    }

    static async removeFromCart(productId, quantity = 1) {
        return await this.apiCall('removeFromCart', {
            id: productId,
            qty: quantity
        });
    }
}

// Torna a classe disponível globalmente
if (typeof window !== 'undefined') {
    window.Shop = Shop;
}

console.log('Shop.js carregado com sucesso!');