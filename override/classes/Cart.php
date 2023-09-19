<?php
/**
 * Easypay
 * @author Trigenius
 *
 * @copyright Direitos autorais (c) 2023 Trigenius
 * 
 * 
 * Todos os direitos reservados.
 * 
 * @license É concedida permissão para utilizar este software de forma gratuita. No entanto, não é permitido
 * modificar, derivar obras de, distribuir, sublicenciar e/ou vender cópias do software.
 * 
 * O SOFTWARE É FORNECIDO "COMO ESTÁ", SEM GARANTIA DE QUALQUER TIPO, EXPRESSA OU IMPLÍCITA,
 * INCLUINDO MAS NÃO SE LIMITANDO A GARANTIAS DE COMERCIALIZAÇÃO, ADEQUAÇÃO A UM PROPÓSITO ESPECÍFICO
 * E NÃO VIOLAÇÃO. EM NENHUM CASO OS AUTORES OU TITULARES DOS DIREITOS AUTORAIS SERÃO RESPONSÁVEIS
 * POR QUALQUER RECLAMAÇÃO, DANOS OU OUTRAS RESPONSABILIDADES, SEJA EM UMA AÇÃO DE CONTRATO, DELITO
 * OU QUALQUER OUTRO MOTIVO, QUE SURJA DE, FORA DE OU EM RELAÇÃO COM O SOFTWARE OU O USO OU OUTRAS
 * NEGOCIAÇÕES NO SOFTWARE.
 */
class Cart extends CartCore
{

    /*
     * Update Product quantity.
     *
     * @param int $quantity Quantity to add (or substract)
     * @param int $id_product Product ID
     * @param int $id_product_attribute Attribute ID if needed
     * @param string $operator Indicate if quantity must be increased or decreased
     *
     * @return bool Whether the quantity has been succesfully updated
     */
    public function updateQty(
        $quantity,
        $id_product,
        $id_product_attribute = null,
        $id_customization = false,
        $operator = 'up',
        $id_address_delivery = 0,
        Shop $shop = null,
        $auto_add_cart_rule = true,
        $skipAvailabilityCheckOutOfStock = false,
        bool $preserveGiftRemoval = true,
        bool $useOrderPrices = false
    ) {
        $id_cart = $this->id;
        $cart = new Cart($id_cart);
        $products_in_cart = $cart->getProducts();

        $have_others = 0;
        $have_subs = 0;
        $actual_product = 0; //0 no Subsc - 1 Subsc 

        //product to add
        foreach (Product::getProductCategoriesFull($id_product) as $categoria) {


            if ($categoria['id_category'] == Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                $actual_product = 1;
            }
        }

        foreach ($products_in_cart as $product) {

            $activador = 0;
            foreach (Product::getProductCategoriesFull($product['id_product']) as $categoria) {

                if ($categoria['id_category'] == Configuration::get('EASYPAY_CATEGORY_SUSCP')) {
                    $have_subs = 1;
                    $activador = 1;
                }
            }
            if ($activador == 0) {
                $have_other = 1;
            }
        }

        if ($actual_product == 1 && $have_other == 1) {
            die('Só pode ter um produto de subscrição no carrinho. Deve remover os outros produtos.');
        } else if ($actual_product == 0 && $have_subs == 1) {
            die('Só pode ter um produto de subscrição no carrinho. Deve remover os outros produtos.');
        }


        return parent::updateQty(
            $quantity,
            $id_product,
            $id_product_attribute,
            $id_customization,
            $operator,
            $id_address_delivery,
            $shop,
            $auto_add_cart_rule,
            $skipAvailabilityCheckOutOfStock,
            $preserveGiftRemoval
        );
    }
}